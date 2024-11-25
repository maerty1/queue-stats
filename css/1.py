# !/usr/bin/env python
# -*- coding: utf-8 -*-
import datetime
import json

import bottle
import numpy as np
import os
import cv2
import sqlite3
from yolov3.utils import Load_Yolo_model
from yolov3.configs import *
import yolov3.utils
import tensorflow as tf
import multiprocessing

app = bottle.Bottle()


def recognize_object(model, name_model: str, image: np, ratio_detect: float = 0.4, input_size: int = 224) -> list:
    def sort_by_left_to_right(val):  # сортировка по возрастанию left - координаты
        return val[0]

    original_image = image.copy()
    try:
        if name_model == 'predict_key_yolo':  # поиск символов нейронной сетью yolo

            image_data = yolov3.utils.image_preprocess(np.copy(original_image), [input_size, input_size])
            image_data = image_data[np.newaxis, ...].astype(np.float32)

            if YOLO_FRAMEWORK == "tf":
                pred_bbox = model.predict(image_data)
            elif YOLO_FRAMEWORK == "trt":
                batched_input = tf.constant(image_data)
                result = model(batched_input)
                pred_bbox = []
                for key, value in result.items():
                    value = value.numpy()
                    pred_bbox.append(value)

            pred_bbox = [tf.reshape(x, (-1, tf.shape(x)[-1])) for x in pred_bbox]
            pred_bbox = tf.concat(pred_bbox, axis=0)

            bboxes = yolov3.utils.postprocess_boxes(pred_bbox, original_image, input_size, ratio_detect)
            bboxes = yolov3.utils.nms(bboxes, 0.45, method='nms')

            list_rect = []

            keys = '0123456789ABCEHKMPTXY'

            for box in bboxes:
                xmin, ymin, xmax, ymax, koeff, key, *_ = box
                text = f'{int(xmin)},{int(ymin)},{int(xmax)},{int(ymax)},{float(koeff)},{keys[int(key)]}'
                list_rect.append((int(xmin), text))
            list_rect.sort(key=sort_by_left_to_right, reverse=False)
            list_rect = [x[1] for x in list_rect]
            return list_rect

        elif name_model == 'predict_plate_yolo':  # поиск номерной пластины нейронной сетью yolo

            image_data = yolov3.utils.image_preprocess(np.copy(original_image), [input_size, input_size])
            image_data = image_data[np.newaxis, ...].astype(np.float32)

            if YOLO_FRAMEWORK == "tf":
                pred_bbox = model.predict(image_data)
            elif YOLO_FRAMEWORK == "trt":
                batched_input = tf.constant(image_data)
                result = model(batched_input)
                pred_bbox = []
                for key, value in result.items():
                    value = value.numpy()
                    pred_bbox.append(value)

            pred_bbox = [tf.reshape(x, (-1, tf.shape(x)[-1])) for x in pred_bbox]
            pred_bbox = tf.concat(pred_bbox, axis=0)

            bboxes = yolov3.utils.postprocess_boxes(pred_bbox, original_image, input_size, ratio_detect)
            bboxes = yolov3.utils.nms(bboxes, 0.45, method='nms')

            list_rect = []

            for box in bboxes:
                xmin, ymin, xmax, ymax, koeff, *_ = box
                list_rect.append(f'{int(xmin)},{int(ymin)},{int(xmax)},{int(ymax)},{float(koeff)}')

            return list_rect

    except BaseException as ex:
        raise ex
    finally:
        pass


def compare_for_template(template: [], key: str) -> (bool, str):
    key_to_template = ''
    for k in key:
        if k in ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9']:
            key_to_template += 'N'
        else:
            key_to_template += 'K'

    for templ in template:
        if len(key_to_template) == len(templ):
            is_template = True
            for i in range(len(templ)):
                if key_to_template[i] != templ[i] and (key_to_template[i] == 'K' and key[i] != '0'):
                    is_template = False
                    break
            if is_template:
                for i in range(len(templ)):
                    if templ[i] == 'K' and key[i] == '0':
                        key = key[:i] + 'O' + key[i + 1:]
                return True, key

    return False, key


def get_moment() -> int:
    dt = datetime.datetime.now()
    return dt.hour * 60 * 60 + dt.minute * 60 + dt.second


def get_name_file_base() -> str:
    return os.path.join(os.getcwd(), 'log.db')


def save_plate(conn, cursor, plate: dict, url: str, login: str, password: str,
               use_print_process_detect: bool = False) -> None:
    if isinstance(plate['save_to_base'], bool) and plate['save_to_base']:
        plate['save_to_base'] = 'is_save'
        date_time, name_file_plate, name_file_care = save_to_dir(f'source_{plate["index_source"]}',
                                                                 plate['image_plate'], plate["image_care"],
                                                                 plate["key"])
        if url != '':
            send_to_server_code = send_data_to_client(plate['key'], plate['index_source'], plate['ratio'], date_time,
                                                      plate['image_plate'],
                                                      plate["image_care"], url, login, password)
        else:
            send_to_server_code = 1
        #
        no_detect_sec = get_moment() - plate['no_detect_sec'] if plate['no_detect_sec'] > 0 else 0
        detect_sec = get_moment() - plate['sec']
        row = [date_time, plate['key'], plate['x0'], plate['y0'], plate['x1'], plate['x1'],
               plate['ratio'], name_file_plate, name_file_care,
               plate['index_source'], plate['detect'], detect_sec, no_detect_sec, send_to_server_code]
        cursor.execute(f'INSERT INTO record VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)', row)
        conn.commit()
        plate['image_plate'] = None
        plate["image_care"] = None
        if use_print_process_detect:
            print(
                f'! save {plate["key"]} date: {date_time} source: {plate["index_source"]} ratio: {round(plate["ratio"], 3)} detect sec: {detect_sec} no detect sec: {no_detect_sec} send_to_server_code: {send_to_server_code}')


def save_to_dir(folder_source: str, image_plate: np, image_care: np, number_car: str) -> ():
    if not os.path.exists(folder_source):
        os.mkdir(folder_source)

    folder_care = os.path.join(folder_source, 'care')
    folder_plate = os.path.join(folder_source, 'plate')
    if not os.path.exists(folder_care):
        os.mkdir(folder_care)
    if not os.path.exists(folder_plate):
        os.mkdir(folder_plate)

    dt = datetime.datetime.now()
    date_time = str(dt)
    for sym in ['.', '-', ':', '-', '/', ' ']:
        date_time = date_time.replace(sym, '_')

    care_rgb = cv2.cvtColor(image_care, cv2.COLOR_BGR2RGB)
    plate_rgb = cv2.cvtColor(image_plate, cv2.COLOR_BGR2RGB)
    name_file_plate = f'{number_car}_{date_time}.jpg'
    name_file_care = f'{number_car}_{date_time}.jpg'

    cv2.imwrite(os.path.join(folder_care, name_file_care), care_rgb)
    cv2.imwrite(os.path.join(folder_plate, name_file_plate), plate_rgb)

    date = dt.strftime('%Y-%m-%d %H:%M:%S') + "Z"
    date = date.replace(' ', 'T')
    return date, name_file_plate, name_file_care


def create_db():
    # это база (формата sqlite3), куда будем записывать все въезды
    name_base = get_name_file_base()
    conn = sqlite3.connect(name_base)
    cursor = conn.cursor()
    cursor.execute("""CREATE TABLE IF NOT EXISTS record(
       datetime TEXT,
       key TEXT,
       x0 INT,
       y0 INT,
       x1 INT,
       y1 INT,
       ratio FLOAT,
       photo_plate TEXT,
       photo_care TEXT,
       source TEXT,
       detect_count INT,
       detect_sec INT,
       no_detect_sec INT,       
       send_to_server_code INT
       );
    """)
    #
    cursor.execute("""CREATE TABLE IF NOT EXISTS num_car(
       number TEXT
       );
    """)
    conn.commit()
    return conn, cursor


def load_list_number_for_mask(cursor):
    rows = cursor.execute('select number from num_car').fetchall()
    return [row[0] for row in rows]


def start_serving(BASE_CLASS_API_HOST: str, BASE_CLASS_API_PORT: int) -> None:
    from paste import httpserver
    application = app
    srv_settings = {'host': BASE_CLASS_API_HOST, 'port': BASE_CLASS_API_PORT}
    httpserver.serve(application, **srv_settings)


def start_server(BASE_CLASS_API_HOST: str, BASE_CLASS_API_PORT: int):
    from multiprocessing import Process
    if BASE_CLASS_API_HOST != '' and BASE_CLASS_API_PORT > 0:
        process = Process(target=start_serving, args=(BASE_CLASS_API_HOST, BASE_CLASS_API_PORT))
        return process
    else:
        return None


def convert_picture_to_base64(name_file: str, source: str, folder: str) -> str:
    file = os.path.join(os.getcwd(), source, folder, name_file)
    if os.path.exists(file):
        try:
            img = cv2.imread(file)
            img = cv2.cvtColor(cv2.COLOR_BGR2RGB, img)
            base64_view = encode_to_base_64(img)
            return base64_view
        except:
            pass

    return ''


@app.route('/get_last_number_car', method='GET', keys=['source'])
def get_last_number_car():
    from bottle import request
    result = {}
    conn, cursor = create_db()
    try:
        source = request.json.get('source')
        rows = conn.execute('select key, ratio, datetime, photo_plate, photo_care from record where source=? and datetime in (select max(datetime) from record where source=?)', [source, source]).fetchall()
        if len(rows) > 0:
            result['key'] = rows[0][0]
            result['ratio'] = rows[0][1]
            result['datetime'] = rows[0][2]
            result['picture_plate'] = convert_picture_to_base64(rows[0][3], source, 'plate')
            result['picture_care'] = convert_picture_to_base64(rows[0][4], source, 'care')
            result['status'] = 'received'
        else:
            result['status'] = 'empty'

    except:
        result['status'] = 'error'
    finally:
        cursor.close()
        conn.close()

    return result


def get_cap(p_q_out, p_event_wait, p_lock, event_stop, p_cam):
    print(f'create process for {p_cam}')
    event_stop.clear()  # сброс сигнала
    while not event_stop.is_set():  # работаем пока не установят сигнал завершения процесса
        p_lock.wait()  # не блокирующее ожидание установки сигнала в событии
        try:
            cap = cv2.VideoCapture(p_cam)
            ret_read, frame_read = cap.read()
        except:
            ret_read, frame_read = False, None
        while not p_q_out.empty():
            p_q_out.get()
        p_q_out.put(ret_read)
        p_q_out.put(frame_read)
        p_event_wait.set()  # сигнал, что выполнили расчет
        p_lock.clear()  # сброс сигнала
    print(f'stop process for {p_cam}')


def main():
    print('-------------')
    #os.chdir('C:\\base_vizavi\\num_care_detect\\project_python')
    os.chdir(os.path.dirname(os.path.abspath(__file__)))
    print(os.getcwd())
    model_yolo_plate = Load_Yolo_model('yolo_plate')
    print('load yolo_plate')
    model_yolo_key = Load_Yolo_model('yolo_key')
    print('load yolo_key')
    # ************************************************** настроечные параметры
    # определяемые шаблоны
    template_number = ['KNNNKKNN', 'KNNNKKNNN']
    # выводить/нет принты в тек. сеансе (для отладки)
    use_print_process_detect = True
    # здесь прописываем список ip - камер
    rect_cam = dict()
    rect_cam['http://192.168.178.148/action/snap?cam=0&user=admin&pwd=admin'] = []
    rect_cam['http://192.168.178.147/action/snap?cam=0&user=admin&pwd=admin'] = []
    rect_cam['http://192.168.178.149/action/snap?cam=0&user=admin&pwd=admin'] = []
    # здесь прописываем размеры области определения для каждого источника (rect_cam) в формате (x0, y0, x1, y1)
    rect_area = [(5, 300, 2500, 1800), (5, 200, 2590, 1700), (600, 300, 1650, 1050)]
    # здесь прописываем имена источников (как они будут записываться в базу данных, и передаваться на сервер)
    source_name = ['First post - Entry (192.168.178.148)', 'Second post - Departure (192.168.178.147)',
                   'Third post - Entry (192.168.178.149)']
    # здесь прописываем получателя post - запроса (без http) вместе с адресом ресурса
    recipient_url = 'http://1cappsrv-pol/svod-pol/hs/Vizavi/ConnectVizavi'
    recipient_login = "IUSR"
    recipient_password = ""
    # количество секунд "выдержки" для записи автомашины
    num_sec_for_save_car_to_database = 6
    # количество секунд НЕ детекции для подтверждения ухода машины с камеры
    sec_no_detect_car = 2
    # Каталог логов
    folder_logs = ''
    # запуск сервера приема внешних сообщений
    process = start_server('localhost', 8087)
    if process is not None:
        process.start()
    # **************************************************
    conn, cursor = create_db()
    #
    list_mask = ['KNNNKK**', 'KNNNKK***']
    #
    list_number = load_list_number_for_mask(cursor) if len(list_mask) > 0 else []
    #
    print(f'load {len(list_number)} number for mask')
    print('start process!')
    #
    list_event_wait = []
    for cam, list_current_plate in rect_cam.items():
        event_wait = multiprocessing.get_context('spawn').Event()
        event_lock = multiprocessing.get_context('spawn').Event()
        event_stop = multiprocessing.get_context('spawn').Event()
        q_out = multiprocessing.get_context('spawn').Queue()
        args = (q_out, event_wait, event_lock, event_stop, cam)
        p = multiprocessing.get_context('spawn').Process(target=get_cap, args=args)
        p.start()
        list_event_wait.append([event_wait, event_lock, event_stop, q_out, cam])
    print(f'start is {len(list_event_wait)} process')
    #
    try:
        num_image = 0
        while True:
            for cam, list_current_plate in rect_cam.items():
                for last_plate in list_current_plate:
                    last_plate['is_found_plate'] = False
            data_cam = []
            for index_source, data in enumerate(list_event_wait):
                event_wait, event_lock, _, q_out, cam = data[:5]
                event_lock.set()
                if event_wait.wait(timeout=30):
                    ret = q_out.get()
                    frame = q_out.get()
                    if ret and frame is not None:
                        data_cam.append([frame, index_source, cam])
                else:
                    print(f'    !!!!! error timeout: get from cam {cam} {datetime.datetime.now()}')
            for data in data_cam:
                frame, index_source, cam = data[:3]
                list_current_plate = rect_cam[cam]
                if len(rect_area) > 0:
                    area = rect_area[index_source]
                    image = frame[area[1]:area[3], area[0]:area[2]]
                else:
                    image = frame
                #cv2.imwrite(os.path.join('E:\\555', f'{index_source}.jpg'), image)
                image = cv2.cvtColor(image, cv2.COLOR_RGB2BGR)
                list_plate = recognize_object(model=model_yolo_plate, image=image, name_model='predict_plate_yolo')
                for plate in list_plate:
                    num_image += 1
                    rect = plate.split(',')
                    y0, y1, x0, x1 = int(rect[1]), int(rect[3]), int(rect[0]), int(rect[2])
                    y0, y1, x0, x1 = min(y0, y1), max(y0, y1), min(x0, x1), max(x0, x1)
                    h, w = 10, 10
                    ay0, ay1, ax0, ax1 = y0 - h, y1 + h, x0 - w, x1 + 3*w
                    ay0, ax0 = max(0, ay0), max(0, ax0)
                    ay1, ax1 = min(ay1, image.shape[0]), min(ax1, image.shape[1])
                    ratio = float(rect[4])
                    image_plate = image[ay0:ay1 + 1, ax0:ax1 + 1]
                    #cv2.imwrite('C:\\base_vizavi\\p.jpg', image_plate)
                    keys = recognize_object(model=model_yolo_key, image=image_plate, name_model='predict_key_yolo')
                    key = ''.join([x.split(',')[5] for x in keys])
                    is_detect_key, key = compare_for_template(template_number, key)
                    if is_detect_key:
                        key = convert_to_mask(list_number, key, list_mask)
                        found_last_plate = False
                        for last_plate in list_current_plate:
                            last_plate['is_found_plate'] = True
                            found_last_plate = True
                            if last_plate['save_to_base'] != 'is_save':
                                delta = get_moment() - last_plate['sec']
                                last_plate['save_to_base'] = delta >= num_sec_for_save_car_to_database
                                if last_plate['ratio'] < ratio:
                                    last_plate['y0'] = y0
                                    last_plate['y1'] = y1
                                    last_plate['x0'] = x0
                                    last_plate['x1'] = x1
                                    #
                                    last_plate['key'] = key
                                    last_plate['ratio'] = ratio
                                    last_plate['image_plate'] = image_plate.copy()
                                    last_plate['image_care'] = image.copy()
                            break

                        if not found_last_plate:
                            val = {}
                            val['key'] = key
                            val['y0'] = y0
                            val['y1'] = y1
                            val['x0'] = x0
                            val['x1'] = x1
                            val['ratio'] = ratio
                            val['save_to_base'] = False
                            val['sec'] = get_moment()
                            val['no_detect_sec'] = 0
                            val['no_detect'] = 0
                            val['detect'] = 0
                            val['is_found_plate'] = True
                            val['image_plate'] = image_plate.copy()
                            val['image_care'] = image.copy()
                            val['index_source'] = source_name[index_source]
                            list_current_plate.append(val)

                for plate in list_current_plate:
                    save_plate(conn, cursor, plate, recipient_url, recipient_login, recipient_password,
                               use_print_process_detect)
                    if not plate['is_found_plate']:
                        if plate['no_detect'] == 0:
                            plate['no_detect_sec'] = get_moment()
                        plate['no_detect'] += 1
                    else:
                        plate['detect'] += 1
                        plate['no_detect'] = 0
                        plate['no_detect_sec'] = 0
                i = 0
                while i < len(list_current_plate):
                    plate = list_current_plate[i]
                    delta = get_moment() - plate['no_detect_sec'] if plate['no_detect_sec'] > 0 else 0
                    if delta >= sec_no_detect_car:
                        if plate['save_to_base'] != 'is_save':
                            plate['save_to_base'] = True
                            save_plate(conn, cursor, plate, recipient_url, recipient_login, recipient_password,
                                       use_print_process_detect)
                        list_current_plate.remove(plate)
                    else:
                        i += 1
    finally:
        for data, index_source in enumerate(list_event_wait):
            event_stop = data[2]
            event_stop.set()  # установка процессам что нужно завершить работы
        cursor.close()
        conn.close()
        if process is not None:
            process.close()


def encode_to_base_64(picture: np) -> str:
    import base64
    name_file = 'C:\\base_vizavi\\num_care_detect\\project_python\\TEST\\time_img.jpg'
    cv2.imwrite(name_file, picture)
    with open(name_file, 'br') as file:
        img_pack_base64 = base64.b64encode(file.read()).decode('utf-8')
        return img_pack_base64


def send_data_to_client(state_number: str, source: str, ratio: float, date_recording: str, picture_number: np,
                        picture_background: np, url: str, login: str, password: str) -> int:
    """
    Функция подготавливает данные, и отсылает их на сервер
    """
    data_dist = {}
    data_dist['state_number'] = state_number
    data_dist['source'] = source
    data_dist['ratio'] = round(ratio, 3)
    data_dist['date_recording'] = date_recording
    try:
        data_dist['pictute_number'] = encode_to_base_64(picture_number)
    except:
        data_dist['pictute_number'] = ''
    try:
        data_dist['picture_background'] = encode_to_base_64(picture_background)
    except:
        data_dist['picture_background'] = ''

    return send_to_client(data_dist, url, login, password)


def send_to_client(data_dict: dict, url: str, login: str, password: str) -> int:
    """
    Функция отсылает данные на сервер
    """
    import requests
    try:
        print(f'-> send to server {url}')
        data_json = json.dumps(data_dict, ensure_ascii=False)
        result = requests.post(url=url, data=data_json, headers={'Authorization': 'Basic'}, auth=(login, password),
                               timeout=10)
        print(f'<- send to server status code {result.status_code}')                                    
        return result.status_code
    except:
        return 0


def load_num_car(name_file_num_car: str) -> None:
    if not os.path.exists(name_file_num_car):
        print(f'not load {name_file_num_car}')
        raise

    with open(name_file_num_car, 'r') as file:
        numbers = file.readlines()
        conn, cursor = create_db()
        try:
            add_number = []
            for number in numbers:
                number = number.replace('\n', '')
                if number != '':
                    rows = cursor.execute('select 0 from num_car where number=?', [number]).fetchall()
                    if len(rows) == 0 and number not in add_number:
                        add_number.append([number])
            if len(add_number) > 0:
                cursor.executemany('insert into num_car(number) values(?)', add_number)
                conn.commit()
        finally:
            cursor.close()
            conn.close()


def convert_to_mask(list_number: [], number: str, list_mask: list) -> str:
    """
    функция конвертирует по маске номер
    """
    for mask in list_mask:
        pos = str(mask).find('*')
        base_number = number[:pos]
        list_num = [ln for ln in list_number if ln[:pos] == base_number]
        if len(list_num) > 0:
            return base_number + list_num[0][pos:]

    return number

if __name__ == '__main__':
    #load_num_car('C:\\base_vizavi\\num_care_detect\\project_python\\car.txt')
    main()
