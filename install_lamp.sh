#!/bin/bash
# install_lamp.sh - Cài LAMP và triển khai TechViet Blog trên Ubuntu sạch
# CHẠY: sudo bash install_lamp.sh
set -e

if [ "$EUID" -ne 0 ]; then
  echo "Vui lòng chạy với quyền root: sudo bash install_lamp.sh"
  exit 1
fi

echo "==> [1/6] Cập nhật hệ thống..."
apt update

echo "==> [2/6] Cài Apache2..."
apt install -y apache2
systemctl enable apache2
systemctl start apache2

echo "==> [3/6] Cài MySQL Server..."
apt install -y mysql-server
systemctl enable mysql
systemctl start mysql

echo "==> [4/6] Cài PHP..."
apt install -y php libapache2-mod-php php-mysql php-mbstring php-xml
systemctl restart apache2

echo "==> [5/6] Tạo database..."
DB_PASS="password"
mysql -u root <<SQL
CREATE DATABASE IF NOT EXISTS blogs CHARACTER SET utf8mb4;
CREATE USER IF NOT EXISTS 'userapp'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON blogs.* TO 'userapp'@'localhost';
FLUSH PRIVILEGES;
SQL
mysql -u root blogs < "$(dirname "$0")/setup_db.sql"

echo "==> [6/6] Triển khai mã nguồn bằng Symbolic Link..."
# Lấy đường dẫn tuyệt đối của thư mục chứa script
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

# Xóa thư mục/link cũ nếu có
rm -rf /var/www/html/blog

# Tạo Symbolic Link tự động trỏ về thư mục www của người clone
ln -s "$SCRIPT_DIR/www" /var/www/html/blog

# Phân quyền tự động cho user chạy sudo và Apache
REAL_USER=${SUDO_USER:-$USER}
chown -R $REAL_USER:www-data "$SCRIPT_DIR/www"
chmod -R 775 "$SCRIPT_DIR/www"
chmod o+rx "/home/$REAL_USER" "$SCRIPT_DIR"

sed -i "s/__DB_PASSWORD__/${DB_PASS}/" "$SCRIPT_DIR/www/config.php"

echo ""
echo "============================================================"
echo " Xong! Truy cập: http://<IP-may-chu>/blog/"
echo " (hoặc trỏ domain của bạn thẳng vào /var/www/html/blog nếu"
echo "  muốn bỏ hẳn tiền tố /blog/ cho giống site thật)"
echo "============================================================"
