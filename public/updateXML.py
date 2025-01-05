import requests
import pymysql
import xml.etree.ElementTree as ET
from datetime import datetime
from requests.packages.urllib3.exceptions import InsecureRequestWarning

requests.packages.urllib3.disable_warnings(InsecureRequestWarning)

DB_CONFIG = {
    'host': 'localhost',
    'user': 'uploader',
    'password': 'uploader',
    'database': 'avito'
}

XML_URL_1 = 'https://prdownload.nodacdn.net/dfiles/7da749ad-284074-7b2184d7/articles.xml'
XML_URL_2 = 'https://www.buszap.ru/get_price?p=28eb21146a7944a9abd330fbf916aa7c&FranchiseeId=9117065'

UPLOAD_PATH = 'https://233204.fornex.cloud/storage/uploads/'

API_URL = "https://abcp50533.public.api.abcp.ru/search/articles/"
API_CREDENTIALS = {
    'userlogin': 'api@abcp50533',
    'userpsw': '6f42e31351bc2469f37f27a7fa7da37c'
}
TARGET_DISTRIBUTOR = 1664240


def download_xml(url):
    response = requests.get(url, timeout=3600, verify=False)
    response.raise_for_status()
    return response.text


def get_price_from_api(brand, articul):
    response = requests.get(API_URL, params={
        'userlogin': API_CREDENTIALS['userlogin'],
        'userpsw': API_CREDENTIALS['userpsw'],
        'number': articul,
        'brand': brand
    }, verify=False)

    # print(f"[DEBUG] Ответ API для {brand}_{articul}: {response.text}")

    if response.ok:
        price_data = response.json()
        for data in price_data:
            if (
                data['distributorId'] == TARGET_DISTRIBUTOR and
                data['brand'] == brand and
                data['number'] == articul
            ):
                return data['price']
    return None


def process_xml(xml_content):
    root = ET.fromstring(xml_content)
    connection = pymysql.connect(**DB_CONFIG)
    cursor = connection.cursor()

    for ad in root.findall('Ad'):
        ad_id = ad.find('Id').text
        brand, articul = ad_id.split('_')

        # Обновляем изображения
        cursor.execute(
            "SELECT brand, articul FROM images WHERE brand=%s AND articul LIKE %s",
            (brand, f"%{articul}%")
        )
        rows = cursor.fetchall()

        if rows:
            images_element = ad.find('Images')
            if images_element is not None:
                images_element.clear()
            else:
                images_element = ET.SubElement(ad, 'Images')

            for row in rows:
                image_url = f"{UPLOAD_PATH}{row[0].lower()}/{row[1].lower()}"
                image = ET.SubElement(images_element, 'Image')
                image.set('url', image_url)

        # Отладка получения цены
        # print(f"[DEBUG] Запрос цены для: {brand}_{articul}")
        price = get_price_from_api(brand, articul)

        # Логирование результата
        if price:
            # print(f"[INFO] Цена обновлена: {brand}_{articul} - {price}")
            price_element = ad.find('Price')
            if price_element is not None:
                ad.remove(price_element)
            ET.SubElement(ad, 'Price').text = str(price)
        # else:
            # print(f"[WARN] Цена не найдена для: {brand}_{articul}")

    connection.close()
    return root


def save_xml(root, file_name):
    tree = ET.ElementTree(root)
    tree.write(f"./{file_name}", encoding='utf-8', xml_declaration=True)


def update_database():
    connection = pymysql.connect(**DB_CONFIG)
    cursor = connection.cursor()
    cursor.execute("UPDATE updates SET date_update = %s WHERE id = 1", (datetime.now(),))
    connection.commit()
    connection.close()


def main():
    try:
        xml_1 = download_xml(XML_URL_1)
        xml_2 = download_xml(XML_URL_2)

        root1 = process_xml(xml_1)
        root2 = process_xml(xml_2)

        for ad in root2.findall('Ad'):
            root1.append(ad)

        save_xml(root1, 'xml.xml')
        update_database()

        print("XML файлы успешно объединены и сохранены.")
    except Exception as e:
        print(f"Произошла ошибка: {str(e)}")


if __name__ == "__main__":
    main()
