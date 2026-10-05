CREATE TABLE topics (
    id SERIAL PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL,
    api_url VARCHAR(255) NOT NULL
);

CREATE TABLE articles (
    id SERIAL PRIMARY KEY,
    topic_id INTEGER NOT NULL REFERENCES topics(id),
    title VARCHAR(300) NOT NULL,
    subtitle VARCHAR(300),
    text TEXT,
    image TEXT,
    link TEXT NOT NULL UNIQUE,
    source VARCHAR(100) NOT NULL,
    loaded_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE readers (
    id SERIAL PRIMARY KEY,
    token VARCHAR(32) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE read_history (
    id SERIAL PRIMARY KEY,
    reader_id INTEGER NOT NULL REFERENCES readers(id) ON DELETE CASCADE,
    article_id INTEGER NOT NULL REFERENCES articles(id) ON DELETE CASCADE,
    read_at TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE (reader_id, article_id)
);
