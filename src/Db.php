<?php

declare(strict_types=1);

namespace AlexSimonovGit\GCD\Db;

use PDO;
use PDOException;

function openDb(): ?PDO
{
    if (!extension_loaded('pdo_sqlite')) {
        return null;
    }

    $path = getDbPath();
    $dir = dirname($path);

    if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
        return null;
    }

    try {
        $pdo = new PDO('sqlite:' . $path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA foreign_keys = ON');
        initSchema($pdo);

        return $pdo;
    } catch (PDOException) {
        return null;
    }
}

function getDbPath(): string
{
    $env = getenv('GCD_DB_PATH');
    if (is_string($env) && $env !== '') {
        return $env;
    }

    $home = getenv('HOME') ?: getenv('USERPROFILE');
    if (is_string($home) && $home !== '') {
        return $home . DIRECTORY_SEPARATOR . '.gcd' . DIRECTORY_SEPARATOR . 'gcd.sqlite';
    }

    return getcwd() . DIRECTORY_SEPARATOR . 'gcd.sqlite';
}

function initSchema(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS players (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS games (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            player_id INTEGER NOT NULL,
            played_at TEXT NOT NULL,
            is_win INTEGER NOT NULL,
            FOREIGN KEY (player_id) REFERENCES players(id)
        )'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS rounds (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            game_id INTEGER NOT NULL,
            a INTEGER NOT NULL,
            b INTEGER NOT NULL,
            correct_gcd INTEGER NOT NULL,
            answer INTEGER NOT NULL,
            is_correct INTEGER NOT NULL,
            FOREIGN KEY (game_id) REFERENCES games(id)
        )'
    );
}

function upsertPlayer(PDO $pdo, string $name): int
{
    $stmt = $pdo->prepare('INSERT OR IGNORE INTO players(name) VALUES(:name)');
    $stmt->execute([':name' => $name]);

    $stmt = $pdo->prepare('SELECT id FROM players WHERE name = :name');
    $stmt->execute([':name' => $name]);

    return (int) $stmt->fetchColumn();
}

function insertGame(PDO $pdo, int $playerId, string $playedAt, bool $isWin): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO games(player_id, played_at, is_win)
         VALUES(:player_id, :played_at, :is_win)'
    );
    $stmt->execute([
        ':player_id' => $playerId,
        ':played_at' => $playedAt,
        ':is_win' => $isWin ? 1 : 0,
    ]);

    return (int) $pdo->lastInsertId();
}

function insertRound(PDO $pdo, int $gameId, int $a, int $b, int $correctGcd, int $answer, bool $isCorrect): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO rounds(game_id, a, b, correct_gcd, answer, is_correct)
         VALUES(:game_id, :a, :b, :correct_gcd, :answer, :is_correct)'
    );
    $stmt->execute([
        ':game_id' => $gameId,
        ':a' => $a,
        ':b' => $b,
        ':correct_gcd' => $correctGcd,
        ':answer' => $answer,
        ':is_correct' => $isCorrect ? 1 : 0,
    ]);
}

function fetchHistory(PDO $pdo, int $limit = 50): array
{
    $sql = '
        SELECT
            g.played_at,
            p.name AS player,
            r.a, r.b,
            r.answer,
            r.is_correct
        FROM rounds r
        JOIN games g ON g.id = r.game_id
        JOIN players p ON p.id = g.player_id
        ORDER BY g.id DESC, r.id ASC
        LIMIT :limit
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}