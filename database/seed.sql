SET NAMES utf8mb4;

INSERT IGNORE INTO banquet_types (code, name, sort, is_system, user_id)
VALUES
    ('marriage', '结婚', 1, 1, NULL),
    ('birth', '降生', 2, 1, NULL),
    ('full_month', '周岁', 3, 1, NULL),
    ('housewarming', '乔迁', 4, 1, NULL),
    ('further_education', '升学', 5, 1, NULL),
    ('other', '治丧', 6, 1, NULL);
