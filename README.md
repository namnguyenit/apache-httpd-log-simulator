# 🌐 Apache HTTPD & Web Server Log Simulator

Dự án cấu hình máy chủ Apache HTTPD và bộ script Python giả lập sinh log truy cập người dùng (Clickstream Access Logs) phục vụ phân tích dữ liệu và hệ thống CloudWatch/Data Pipeline.

## 📁 Cấu trúc thư mục
- `httpd.conf`: File cấu hình máy chủ Apache HTTPD tối ưu cho ứng dụng web Café.
- `cafe/`: Mã nguồn website PHP mô phỏng AnyCompany Café (giao diện, thực đơn, giỏ hàng, đặt hàng).
- `log-gen_geo.py`: Script giả lập sinh log truy cập toàn cầu kèm toạ độ địa lý (Latitude, Longitude, City, Country).
- `log_gen VN.py`: Script giả lập lưu lượng truy cập từ các địa chỉ IP và tỉnh/thành phố tại Việt Nam.

## 🛠️ Sử dụng
```bash
# Chạy script sinh log địa lý
python log-gen_geo.py

# Chạy script sinh log Việt Nam
python "log_gen VN.py"
```

## 👨‍💻 Tác giả
- [Cao Đức Trung (@namnguyenit)](https://github.com/namnguyenit)
