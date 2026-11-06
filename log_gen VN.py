import time
import datetime
import random
import gzip
import zipfile
import sys
sys.path.insert(0, '/home/ec2-user/.local/lib/python3.7/site-packages')
import pytz
import numpy
import argparse
import socket
import requests
from faker import Faker
from random import randrange
from tzlocal import get_localzone
local = get_localzone()
from ip2geotools.databases.noncommercial import DbIpCity
local = get_localzone()
import json

class switch(object):
    def __init__(self, value):
        self.value = value
        self.fall = False

    def __iter__(self):
        """Return the match method once, then stop"""
        yield self.match
        return  # Sửa lỗi StopIteration cho Python 3.7+

    def match(self, *args):
        """Indicate whether or not to enter a case suite"""
        if self.fall or not args:
            return True
        elif self.value in args: # changed for v1.5, see below
            self.fall = True
            return True
        else:
            return False

parser = argparse.ArgumentParser(__file__, description="Fake Apache Log Generator")
parser.add_argument("--output", "-o", dest='output_type', help="Write to a Log file, a gzip file or to STDOUT", choices=['LOG','GZ','CONSOLE'] )
parser.add_argument("--log-format", "-l", dest='log_format', help="Log format, Common or Extended Log Format ", choices=['CLF','ELF'], default="ELF" )
parser.add_argument("--num", "-n", dest='num_lines', help="Number of lines to generate (0 for infinite)", type=int, default=1)
parser.add_argument("--prefix", "-p", dest='file_prefix', help="Prefix the output file name", type=str)
parser.add_argument("--sleep", "-s", help="Sleep this long between lines (in seconds)", default=0.0, type=float)

args = parser.parse_args()

log_lines = args.num_lines
file_prefix = args.file_prefix
output_type = args.output_type
log_format = args.log_format

faker = Faker()

timestr = time.strftime("%Y%m%d-%H%M%S")
otime = datetime.datetime.now()
def get_rtime():
    rtime = faker.date_time_between(start_date="-1w", end_date="now")
    return rtime

outFileName = 'test-log.log'

for case in switch(output_type):
    if case('LOG'):
        f = open(outFileName,'w')
        break
    if case('GZ'):
        f = gzip.open(outFileName+'.gz','w')
        break
    if case('CONSOLE'): pass
    if case():
        f = sys.stdout

# modern iPhone with Safari
iPhone = 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_2 like Mac OS X) AppleWebKit/536.1 (KHTML, like Gecko) CriOS/13.0.848.0 Mobile/65R242 Safari/16.2'
# modern Android device with Firefox 
Android = 'Mozilla/5.0 (Android 12.0.0; Mobile; rv:53.0) Gecko/53.0 Firefox/53.0'
# modern windows PC with Chrome
Windows = 'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/531.2 (KHTML, like Gecko) Chrome/111.0.553.2.0.854.0'
# modern Macintosh with Firefox
mac = 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 12.6.1; rv:1.9.3.20) Gecko/6156-07-12 15:15:32 Firefox/102.6.0)'

ualist_new = [
    iPhone,
    Android,
    Windows, 
    mac
]

hostname = socket.gethostname()
ipAddress = socket.gethostbyname(hostname)

# Danh sách sản phẩm mẫu đúng như menu thực tế
product_catalog = [
    {"product_id": "1", "product_name": "Croissant", "price": 1.5},
    {"product_id": "2", "product_name": "Donut", "price": 1.0},
    {"product_id": "3", "product_name": "Chocolate Chip Cookie", "price": 2.5},
    {"product_id": "4", "product_name": "Muffin", "price": 3.0},
    {"product_id": "5", "product_name": "Strawberry Blueberry Tart", "price": 3.5},
    {"product_id": "6", "product_name": "Strawberry Tart", "price": 3.5},
    {"product_id": "7", "product_name": "Coffee", "price": 3.0},
    {"product_id": "8", "product_name": "Hot Chocolate", "price": 3.0},
    {"product_id": "9", "product_name": "Latte", "price": 3.5}
]

geo_cache = {}
geo_fail_count = 0  # Đếm số lần trả về geo mẫu
GEO_FAIL_LIMIT = 100  # Giới hạn số lần trả về geo mẫu

# Danh sách một số thành phố lớn ở Việt Nam với lat/lon trung tâm
vn_cities = [
    ("Hà Nội", 21.028511, 105.804817),
    ("Hồ Chí Minh", 10.776889, 106.700806),
    ("Đà Nẵng", 16.047079, 108.206230),
    ("Hải Phòng", 20.844911, 106.688084),
    ("Cần Thơ", 10.045162, 105.746857),
    ("Nha Trang", 12.238791, 109.196749),
    ("Huế", 16.463713, 107.590866),
    ("Vũng Tàu", 10.411379, 107.136227),
    ("Biên Hòa", 10.945539, 106.822696),
    ("Buôn Ma Thuột", 12.666389, 108.037778),
    ("Pleiku", 13.983333, 108.000000),
    ("Quy Nhơn", 13.782222, 109.219167),
    ("Long Xuyên", 10.386389, 105.435194),
    ("Rạch Giá", 10.01245, 105.08091),
    ("Thái Nguyên", 21.594222, 105.848068)
]

def get_geo_from_ip(ip):
    city, base_lat, base_lon = random.choice(vn_cities)
    # Random nhỏ quanh vị trí trung tâm thành phố
    lat = round(base_lat + random.uniform(-0.05, 0.05), 6)
    lon = round(base_lon + random.uniform(-0.05, 0.05), 6)
    return 'Vietnam', city, city, str(lat), str(lon)

flag = True
while (flag):
    if args.sleep:
        increment = datetime.timedelta(seconds=args.sleep)
    else:
        increment = datetime.timedelta(seconds=random.randint(0, 1))
    otime += increment

    ip = faker.ipv4()
    geo_country, geo_region, geo_city, geo_lat, geo_long = get_geo_from_ip(ip)
    
    # accessing cafe landing page on the webserver.
    uri = "/cafe"
    fn = "/var/www/html/cafe"
    dt = get_rtime()
    vrb ="GET"
    if uri.find("apps")>0:
        uri += str(random.randint(1000,10000))
    resp = "200"
    byt = int(random.gauss(5000,50))
    referer = "-"
    # 40 percent probablity for iPhone, 30 percent for Android (biasing mobile for people on the go). 20 percent Windows PC and 10 percent Mac. This is loosely based upon research at https://gs.statcounter.com/platform-market-share/desktop-mobile-tablet and https://www.statista.com/statistics/1228163/global-device-installed-base-by-type-worldwide/.
    useragent = numpy.random.choice(ualist_new,p=[0.4,0.3,0.2,0.1])
    if log_format == "CLF":
        f.write('%s - - [%s] "%s %s HTTP/1.0" %s %s\n' % (ip,dt,vrb,uri,resp,byt))
    elif log_format == "ELF":
        # Dạng phẳng: log truy cập thường
        f.write('{{ "time":"{}", "process":"{}", "filename":"{}", "remoteIP":"{}", "host":"{}", "request":"{}", "query":"", "method":"{}", "status":"{}", "useragent":"{}", "referer":"{}", "country":"{}", "region":"{}", "city":"{}", "lat": "{}", "lon":"{}"}}\n'.format(
            dt, byt, fn, ip, ipAddress, uri, vrb, resp, useragent, referer, geo_country, geo_region, geo_city, geo_lat, geo_long))
    f.flush()
    
    # Accessing the menu. Note: not all users will access the menu, so using random function, if 1 access menu.  If 0, user leaves the website.
    Step2 = bool(random.getrandbits(1))
    if Step2 == 1:
        uri = "/cafe/menu.php"
        fn = "proxy:fcgi://localhost/var/www/html/cafe/menu.php"
        dt = get_rtime()
        vrb ="GET"
        if uri.find("apps")>0:
            uri += str(random.randint(1000,10000))
        resp = "200"
        byt = int(random.gauss(5000,50))
        referer = "http://" + ipAddress + "/cafe/index.php"
        if log_format == "CLF":
            f.write('%s - - [%s] "%s %s HTTP/1.0" %s %s\n' % (ip,dt,vrb,uri,resp,byt))
        elif log_format == "ELF": 
            f.write('{{ "time":"{}", "process":"{}", "filename":"{}", "remoteIP":"{}", "host":"{}", "request":"{}", "query":"", "method":"{}", "status":"{}", "useragent":"{}", "referer":"{}", "country":"{}", "region":"{}", "city":"{}", "lat": "{}", "lon":"{}"}}\n'.format(
                dt, byt, fn, ip, ipAddress, uri, vrb, resp, useragent, referer, geo_country, geo_region, geo_city, geo_lat, geo_long))
        f.flush()
        
        # Making a purchase from the menu.  processOrder.php through method POST only occurs when a purchase is made.  Again using random function to simulate real world usage.
        Step3 = bool(random.getrandbits(1))
        if Step3 == 1:
            uri = "/cafe/processOrder.php"
            fn = "proxy:fcgi://localhost/var/www/html/cafe/processOrder.php"
            dt = get_rtime()
            vrb ="POST"
            if uri.find("apps")>0:
                uri += str(random.randint(1000,10000))
            resp = "200"
            byt = int(random.gauss(5000,50))
            referer = "http://" + ipAddress + "/cafe/menu.php"
            if log_format == "CLF":
                f.write('%s - - [%s] "%s %s HTTP/1.0" %s %s\n' % (ip,dt,vrb,uri,resp,byt))
            elif log_format == "ELF":
                # Dạng phẳng: mỗi sản phẩm 1 dòng, không có mảng products
                order_id = str(random.randint(10000,99999))
                num_products = random.randint(1, 3)
                products = random.sample(product_catalog, num_products)
                order_total = 0.0
                for p in products:
                    quantity = random.randint(1, 4)
                    amount = p["price"] * quantity
                    order_total += amount
                    bakery_names = ["Croissant", "Donut", "Chocolate Chip Cookie", "Muffin", "Strawberry Blueberry Tart", "Strawberry Tart"]
                    drink_names = ["Coffee", "Hot Chocolate", "Latte"]
                    if p["product_name"] in bakery_names:
                        p_type = "Bakery"
                    elif p["product_name"] in drink_names:
                        p_type = "Drink"
                    else:
                        p_type = "Other"
                    f.write('{{ "time":"{}", "process":"{}", "filename":"{}", "remoteIP":"{}", "host":"{}", "request":"{}", "query":"", "method":"{}", "status":"{}", "useragent":"{}", "referer":"{}", "country":"{}", "region":"{}", "city":"{}", "lat": "{}", "lon":"{}", "order_id": "{}", "order_total": {}, "product_id": {}, "product_name": "{}", "quantity": {}, "price": {}, "amount": {}, "type": "{}" }}\n'.format(
                        dt, byt, fn, ip, ipAddress, uri, vrb, resp, useragent, referer, geo_country, geo_region, geo_city, geo_lat, geo_long, str(order_id), round(order_total,2), int(p["product_id"]), p["product_name"], int(quantity), float(p["price"]), round(amount,2), p_type))
            f.flush()        
            
            # Making a second purchase from the menu.  This is a repeat customer. Again using random function to simulate real world usage.
            Step4 = bool(random.getrandbits(1))
            if Step4 == 1:
                uri = "/cafe/processOrder.php"
                fn = "proxy:fcgi://localhost/var/www/html/cafe/processOrder.php"
                dt = get_rtime()
                vrb ="POST"
                if uri.find("apps")>0:
                    uri += str(random.randint(1000,10000))
                resp = "200"
                byt = int(random.gauss(5000,50))
                referer = "http://" + ipAddress + "/cafe/menu.php"
                if log_format == "CLF":
                    f.write('%s - - [%s] "%s %s HTTP/1.0" %s %s\n' % (ip,dt,vrb,uri,resp,byt))
                elif log_format == "ELF": 
                    f.write('{{ "time":"{}", "process":"{}", "filename":"{}", "remoteIP":"{}", "host":"{}", "request":"{}", "query":"", "method":"{}", "status":"{}", "useragent":"{}", "referer":"{}", "country":"{}", "region":"{}", "city":"{}", "lat": "{}", "lon":"{}"}}\n'.format(
                        dt, byt, fn, ip, ipAddress, uri, vrb, resp, useragent, referer, geo_country, geo_region, geo_city, geo_lat, geo_long))
                f.flush()
            else:
                Step4 == 0
        else:
            Step3 == 0
    else:
        Step2 == 0

    # log_lines reduced by 1, as the loop finishes.
    log_lines = log_lines - 1
    # When log_lines reaches 0 end loop.
    flag = False if log_lines == 0 else True
    if args.sleep:
        time.sleep(args.sleep)

# Hỏi người dùng nhập số lượng log nếu không truyền qua --num
if args.num_lines == 1 and not any(arg.startswith('--num') or arg.startswith('-n') for arg in sys.argv):
    try:
        user_input = input('Nhập số lượng log cần sinh: ')
        log_lines = int(user_input)
    except Exception:
        print('Giá trị nhập không hợp lệ, mặc định sinh 1 log.')
        log_lines = 1
