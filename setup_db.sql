CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    excerpt VARCHAR(400) NOT NULL,
    content TEXT NOT NULL,
    author VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id)
);

INSERT INTO posts (title, slug, excerpt, content, author) VALUES
('5 mẹo tối ưu tốc độ máy tính cho dân văn phòng',
 'toi-uu-toc-do-may-tinh',
 'Máy chạy chậm làm giảm năng suất làm việc đáng kể. Dưới đây là 5 cách đơn giản giúp bạn lấy lại tốc độ.',
 'Máy tính chậm là nỗi ám ảnh của nhiều người làm văn phòng. Trong bài viết này, chúng tôi chia sẻ 5 mẹo đơn giản: dọn dẹp ứng dụng khởi động cùng hệ thống, xóa file tạm định kỳ, nâng cấp ổ cứng lên SSD, giới hạn số tab trình duyệt mở cùng lúc, và bật chế độ tiết kiệm hiệu năng khi không cần đồ họa nặng. Áp dụng đều đặn sẽ giúp máy mượt hơn rõ rệt sau một tuần sử dụng.',
 'Minh Anh'),
('Làm việc từ xa hiệu quả: kinh nghiệm sau 2 năm remote',
 'kinh-nghiem-remote-2-nam',
 'Remote work không chỉ là mặc đồ thoải mái ở nhà. Đây là những bài học thực tế mình đúc kết được.',
 'Sau hai năm làm việc từ xa, mình nhận ra kỷ luật cá nhân quan trọng hơn công cụ. Một lịch trình cố định, không gian làm việc riêng biệt, và thói quen "tắt máy đúng giờ" giúp mình tránh kiệt sức. Ngoài ra, giao tiếp chủ động với đồng nghiệp qua tin nhắn ngắn gọn, rõ ràng cũng giúp công việc trôi chảy hơn nhiều so với họp hành liên tục.',
 'Thuỳ Dương'),
('Review nhanh: Có nên nâng cấp lên RAM 32GB năm 2026?',
 'review-nang-cap-ram-32gb',
 'RAM 16GB từng là tiêu chuẩn, nhưng với các ứng dụng hiện nay liệu đã đủ dùng?',
 'Với xu hướng ứng dụng ngày càng ngốn RAM, đặc biệt là các công cụ chỉnh sửa ảnh/video và nhiều tab trình duyệt, 16GB RAM bắt đầu cho thấy giới hạn. Trong bài test thực tế, nâng cấp lên 32GB giúp giảm đáng kể tình trạng giật lag khi multitask. Tuy nhiên nếu bạn chỉ dùng máy để soạn thảo văn bản và lướt web cơ bản, 16GB vẫn hoàn toàn đáp ứng tốt.',
 'Quang Huy');

INSERT INTO comments (post_id, name, comment) VALUES
(1, 'Văn Tùng', 'Bài viết hữu ích quá, mình áp dụng thử mẹo xóa file tạm thấy máy nhẹ hẳn.'),
(1, 'Lan Phương', 'Cho mình hỏi dọn file tạm bằng công cụ nào an toàn vậy ạ?'),
(2, 'Đức Anh', 'Đồng cảm ghê, mình cũng từng kiệt sức vì không có ranh giới giờ giấc khi mới remote.');
