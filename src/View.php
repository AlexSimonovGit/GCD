<?php

declare(strict_types=1);

namespace AlexSimonovGit\GCD\View;

use function cli\line;
use function cli\prompt;

function showMainMenu(): void
{
    line('');
    line('===== GCD =====');
    line('1) Играть');
    line('2) Посмотреть прошлые партии');
    line('3) Выход');
}

function readMenuChoice(): string
{
    return (string) prompt('Выберите пункт (1-3)');
}

function showGameIntro(): void
{
    line('');
    line('Найдите наибольший общий делитель данных чисел.');
}

function askPlayerName(): string
{
    return (string) prompt('Введи свое имя');
}

function greet(string $name): void
{
    line('Привет, %s!', $name);
}

function showQuestion(int $a, int $b): void
{
    line('');
    line('Числа: %d и %d', $a, $b);
}

function askAnswer(): string
{
    return (string) prompt('Ваш ответ');
}

function showCorrect(): void
{
    line('Верно!');
}

function showWrong(int $correct): void
{
    line('Неверно. Правильный ответ: %d', $correct);
}

function showGameResult(string $name, bool $isWin): void
{
    line('');
    if ($isWin) {
        line('Поздравляю, %s! Ты победил!', $name);
        return;
    }

    line('%s, в следующий раз получится лучше.', $name);
}

function showDbUnavailable(): void
{
    line('');
    line('История недоступна: расширение pdo_sqlite не подключено или БД не удалось открыть.');
}

function showHistory(array $rows): void
{
    line('');
    if ($rows === []) {
        line('История пуста.');
        return;
    }

    line('Время | Игрок | Числа | Ответ | Результат');
    line(str_repeat('-', 60));

    foreach ($rows as $row) {
        $time = (string) ($row['played_at'] ?? '');
        $player = (string) ($row['player'] ?? '');
        $a = (int) ($row['a'] ?? 0);
        $b = (int) ($row['b'] ?? 0);
        $answer = (int) ($row['answer'] ?? 0);
        $ok = ((int) ($row['is_correct'] ?? 0)) === 1 ? 'верно' : 'неверно';

        line('%s | %s | %d,%d | %d | %s', $time, $player, $a, $b, $answer, $ok);
    }
}