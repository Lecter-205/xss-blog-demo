# TechViet Blog — Website demo chứa XSS ẩn (phục vụ báo cáo)

Trang blog trông hoàn toàn bình thường với người ngoài. Không có bất kỳ nhãn "lab/demo/exploit" nào trên giao diện. Lỗ hổng được cài cắm tự nhiên vào các tính năng thật của blog.

## Cài đặt

```bash
cd xss-blog
chmod +x install_lamp.sh
sudo bash install_lamp.sh
```

Truy cập: `http://<IP-hoặc-domain>/blog/`

## 3 lỗ hổng ẩn trong site (chi tiết + bản vá xem file `PATCH_NOTES.md`)

1. **Reflected XSS** — ô tìm kiếm (`search.php?q=...`)
2. **Stored XSS** — form bình luận dưới mỗi bài viết (`post.php`)
3. **DOM-based XSS** — tính năng highlight từ khóa khi vào bài viết kèm `?utm_term=...`

## Hệ thống đăng nhập (để demo chiếm quyền phiên qua cookie)

Site có đăng ký/đăng nhập (`register.php`, `login.php`, `profile.php`) với session cookie **cố ý tắt `HttpOnly`** (xem `PATCH_NOTES.md` mục 4), để `document.cookie` đọc được `PHPSESSID` — cho phép demo kịch bản đầy đủ: **XSS → đánh cắp cookie → chiếm quyền tài khoản** (không chỉ dừng ở `alert()`).

Quy trình demo gợi ý:
1. Đăng ký 1 tài khoản demo tại `/blog/register.php`, đăng nhập vào `/blog/profile.php` để có session.
2. Gửi payload XSS (reflected/stored/DOM) có `document.cookie` tới nơi bạn hứng dữ liệu (ví dụ webhook.site, hoặc server log riêng của bạn).
3. Lấy giá trị `PHPSESSID` nhận được, sửa cookie trên trình duyệt khác (DevTools → Storage/Application → Cookies).
4. Mở `/blog/profile.php` trên trình duyệt đó → đăng nhập thành công mà không cần mật khẩu.

`PATCH_NOTES.md` **không được đặt trong thư mục `www/`** nên sẽ không bị public cùng trang web — tách biệt hoàn toàn khỏi mã nguồn site để dùng riêng cho báo cáo hoặc khi cần vá lại.
