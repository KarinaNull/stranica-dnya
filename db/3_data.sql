INSERT INTO topics (code, name, api_url) VALUES
('wiki', 'Википедия', 'https://ru.wikipedia.org/api/rest_v1/page/random/summary'),
('travel', 'Путешествия', 'https://ru.wikivoyage.org/api/rest_v1/page/random/summary'),
('space', 'Космос', 'https://api.nasa.gov/planetary/apod'),
('kanji', 'Кандзи', 'https://kanjiapi.dev/v1/kanji/'),
('english', 'Английские слова', 'https://en.wiktionary.org/api/rest_v1/page/definition/');

INSERT INTO readers (token, created_at) VALUES
('test1', '2026-09-20 10:00'),
('test2', '2026-09-25 18:30'),
('test3', '2026-10-01 09:15');

INSERT INTO articles (topic_id, title, subtitle, text, link, source, loaded_at) VALUES
(1, 'Перекоп', 'населённый пункт на Перекопском перешейке', 'Перекоп - город, существовавший до 1920 года на Перекопском перешейке.', 'https://ru.wikipedia.org/wiki/Перекоп', 'ru.wikipedia.org', '2026-09-28 12:00'),
(1, 'Herzeleid', 'альбом Rammstein', 'Herzeleid - дебютный альбом немецкой метал-группы Rammstein, выпущенный в 1995 году.', 'https://ru.wikipedia.org/wiki/Herzeleid', 'ru.wikipedia.org', '2026-10-01 09:20'),
(2, 'Шадринск', 'город в Курганской области', 'Шадринск - второй по величине город Курганской области.', 'https://ru.wikivoyage.org/wiki/Шадринск', 'ru.wikivoyage.org', '2026-09-29 15:40'),
(3, 'The Horsehead Nebula', 'Фото дня NASA от 21.12.2024', 'One of the most identifiable nebulae in the sky.', 'https://apod.nasa.gov/apod/ap241221.html', 'apod.nasa.gov', '2026-09-30 08:10'),
(4, '事', 'matter', 'Значение: matter, thing, fact, business, reason, possibly.', 'https://jisho.org/search/%E4%BA%8B%20%23kanji', 'kanjiapi.dev', '2026-10-01 11:00'),
(5, 'mundane', 'Adjective, Noun', 'Adjective: Worldly, earthly, profane.', 'https://en.wiktionary.org/wiki/mundane', 'en.wiktionary.org', '2026-10-02 10:30'),
(1, 'Вёгтленсоффен', 'коммуна во Франции', 'Вёгтленсоффен - коммуна на северо-востоке Франции.', 'https://ru.wikipedia.org/wiki/Вёгтленсоффен', 'ru.wikipedia.org', '2026-10-02 11:10');

INSERT INTO read_history (reader_id, article_id, read_at) VALUES
(1, 1, '2026-09-28 12:15'),
(1, 3, '2026-09-29 16:00'),
(1, 4, '2026-09-30 08:30'),
(1, 6, '2026-10-02 10:40'),
(2, 1, '2026-09-28 20:05'),
(2, 2, '2026-10-01 09:45'),
(2, 5, '2026-10-01 11:20'),
(3, 1, '2026-10-01 09:30');
