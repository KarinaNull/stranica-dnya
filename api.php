<?php

$words = [
    'serendipity', 'resilient', 'ambiguous', 'meticulous', 'eloquent', 'curious', 'diligent',
    'genuine', 'vivid', 'humble', 'subtle', 'reluctant', 'abundant', 'candid', 'fragile',
    'obscure', 'persistent', 'tedious', 'versatile', 'whimsical', 'brisk', 'cozy', 'daunting',
    'eager', 'feasible', 'gloomy', 'hectic', 'inevitable', 'keen', 'lenient', 'mundane',
    'notorious', 'ominous', 'peculiar', 'quaint', 'rigid', 'serene', 'thrive', 'undermine',
    'vague', 'wander', 'yearn', 'zealous', 'cherish', 'endeavor', 'flourish', 'grasp',
    'linger', 'ponder', 'scrutiny', 'tremendous', 'wholesome', 'bewildered', 'clumsy',
];

function get_json($url)
{
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'header' => "User-Agent: StranicaDnya/1.0 (student project)\r\n",
            'ignore_errors' => true,
        ],
    ]);

    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return null;
    }

    return json_decode($response, true);
}

function short_text($text, $max = 600)
{
    $text = trim($text);
    if (mb_strlen($text) <= $max) {
        return $text;
    }
    $text = mb_substr($text, 0, $max);
    $space = mb_strrpos($text, ' ');
    if ($space !== false) {
        $text = mb_substr($text, 0, $space);
    }
    return $text . '...';
}

function from_wikimedia($site, $topic)
{
    $data = get_json('https://' . $site . '/api/rest_v1/page/random/summary');
    if (!$data || empty($data['title'])) {
        return null;
    }

    return [
        'topic' => $topic,
        'title' => $data['title'],
        'subtitle' => $data['description'] ?? '',
        'text' => short_text($data['extract'] ?? ''),
        'image' => $data['thumbnail']['source'] ?? '',
        'link' => 'https://' . $site . '/wiki/' . str_replace(' ', '_', $data['title']),
        'source' => $site,
    ];
}

function from_nasa()
{
    $data = get_json('https://api.nasa.gov/planetary/apod?api_key=DEMO_KEY&count=1&thumbs=true');
    if (!$data || empty($data[0]['title'])) {
        return null;
    }
    $data = $data[0];

    if ($data['media_type'] == 'image') {
        $image = $data['url'];
    } else {
        $image = $data['thumbnail_url'] ?? '';
    }

    $date = str_replace('-', '', substr($data['date'], 2));

    return [
        'topic' => 'space',
        'title' => $data['title'],
        'subtitle' => 'Фото дня NASA от ' . date('d.m.Y', strtotime($data['date'])),
        'text' => short_text($data['explanation'] ?? ''),
        'image' => $image,
        'link' => 'https://apod.nasa.gov/apod/ap' . $date . '.html',
        'source' => 'apod.nasa.gov',
    ];
}

function from_kanji()
{
    $list = get_json('https://kanjiapi.dev/v1/kanji/joyo');
    if (!$list) {
        return null;
    }

    $kanji = $list[array_rand($list)];
    $data = get_json('https://kanjiapi.dev/v1/kanji/' . urlencode($kanji));
    if (!$data || empty($data['kanji'])) {
        return null;
    }

    $text = 'Значение: ' . implode(', ', $data['meanings']) . '.';
    if (!empty($data['on_readings'])) {
        $text .= "\nОнъёми: " . implode(', ', $data['on_readings']) . '.';
    }
    if (!empty($data['kun_readings'])) {
        $text .= "\nКунъёми: " . implode(', ', $data['kun_readings']) . '.';
    }
    $text .= "\nКоличество черт: " . $data['stroke_count'] . '.';
    if (!empty($data['jlpt'])) {
        $text .= "\nУровень JLPT: N" . $data['jlpt'] . '.';
    }

    return [
        'topic' => 'kanji',
        'title' => $data['kanji'],
        'subtitle' => $data['meanings'][0] ?? '',
        'text' => $text,
        'image' => '',
        'link' => 'https://jisho.org/search/' . urlencode($data['kanji'] . ' #kanji'),
        'source' => 'kanjiapi.dev',
    ];
}

function from_dictionary()
{
    global $words;

    $word = $words[array_rand($words)];
    $data = get_json('https://en.wiktionary.org/api/rest_v1/page/definition/' . $word);
    if (!$data || empty($data['en'])) {
        return null;
    }

    $text = '';
    $parts = [];
    foreach ($data['en'] as $meaning) {
        $parts[] = $meaning['partOfSpeech'];
        $text .= $meaning['partOfSpeech'] . ': ' . html_entity_decode(strip_tags($meaning['definitions'][0]['definition']));
        if (!empty($meaning['definitions'][0]['examples'][0])) {
            $text .= ' Example: ' . html_entity_decode(strip_tags($meaning['definitions'][0]['examples'][0]));
        }
        $text .= "\n";
    }

    return [
        'topic' => 'english',
        'title' => $word,
        'subtitle' => implode(', ', array_unique($parts)),
        'text' => short_text($text),
        'image' => '',
        'link' => 'https://en.wiktionary.org/wiki/' . $word,
        'source' => 'en.wiktionary.org',
    ];
}

function load_article($topic)
{
    if ($topic == 'travel') {
        return from_wikimedia('ru.wikivoyage.org', 'travel');
    }
    if ($topic == 'space') {
        return from_nasa();
    }
    if ($topic == 'kanji') {
        return from_kanji();
    }
    if ($topic == 'english') {
        return from_dictionary();
    }
    return from_wikimedia('ru.wikipedia.org', 'wiki');
}
