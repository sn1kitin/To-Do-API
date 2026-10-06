<?php
//   ?cmd=all                    alle items ophalen
//   ?cmd=done&id=1              item op DONE zetten
//   ?cmd=notdone&id=1           item terug op NOT DONE zetten
//   ?cmd=remove&id=1            item verwijderen
//   ?cmd=add&item=              item toevoegen

declare(strict_types=1);

require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

// Stuurt het antwoord als JSON en stopt het script.
function respond(array $data): void
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Haalt id op en controleert of het een geldig positief getal is.
function requireId(): int
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id === false || $id === null || $id < 1) {
        respond([
            'success' => false,
            'message' => "Parameter 'id' ontbreekt of is geen geldig getal.",
        ]);
    }

    return $id;
}

// Controleert of een item bestaat en geeft de huidige status terug.
function findStatus(PDO $pdo, int $id): ?string
{
    $statement = $pdo->prepare('SELECT status FROM todos WHERE id = ?');
    $statement->execute([$id]);
    $row = $statement->fetch();

    return $row === false ? null : $row['status'];
}

try {
    $pdo = getConnection();
    $cmd = $_GET['cmd'] ?? '';

    switch ($cmd) {

        // Alle items teruggeven.
        case 'all':
            $statement = $pdo->query('SELECT id, item, status FROM todos ORDER BY id');
            $todos = $statement->fetchAll();

            foreach ($todos as &$todo) {
                $todo['id'] = (int) $todo['id'];
            }
            unset($todo);

            respond($todos);

        // Status van een item op DONE of NOT DONE zetten.
        case 'done':
        case 'notdone':
            $id     = requireId();
            $status = $cmd === 'done' ? 'DONE' : 'NOT DONE';
            $huidig = findStatus($pdo, $id);

            if ($huidig === null) {
                respond([
                    'success' => false,
                    'message' => "To Do item met ID {$id} bestaat niet.",
                ]);
            }

            if ($huidig === $status) {
                respond([
                    'success' => true,
                    'message' => "To Do item met ID {$id} stond al op {$status}.",
                    'id'      => $id,
                    'status'  => $status,
                ]);
            }

            $statement = $pdo->prepare('UPDATE todos SET status = ? WHERE id = ?');
            $statement->execute([$status, $id]);

            respond([
                'success' => true,
                'message' => "To Do item met ID {$id} is op {$status} gezet.",
                'id'      => $id,
                'status'  => $status,
            ]);

        // Item verwijderen.
        case 'remove':
            $id = requireId();

            $statement = $pdo->prepare('DELETE FROM todos WHERE id = ?');
            $statement->execute([$id]);

            if ($statement->rowCount() === 0) {
                respond([
                    'success' => false,
                    'message' => "To Do item met ID {$id} bestaat niet.",
                ]);
            }

            respond([
                'success' => true,
                'message' => "To Do item met ID {$id} is succesvol verwijderd.",
                'id'      => $id,
            ]);

        // Nieuw item toevoegen.
        case 'add':
            $item = trim((string) ($_GET['item'] ?? ''));

            if ($item === '') {
                respond([
                    'success' => false,
                    'message' => "Parameter 'item' ontbreekt of is leeg.",
                ]);
            }

            $statement = $pdo->prepare("INSERT INTO todos (item, status) VALUES (?, 'NOT DONE')");
            $statement->execute([$item]);

            respond([
                'success' => true,
                'message' => 'Nieuw To Do item toegevoegd.',
                'todo'    => [
                    'id'     => (int) $pdo->lastInsertId(),
                    'item'   => $item,
                    'status' => 'NOT DONE',
                ],
            ]);

        default:
            respond([
                'success'           => false,
                'message'           => "Onbekende opdracht.",
                'geldige_commandos' => ['all', 'done', 'notdone', 'remove', 'add'],
            ]);
    }

} catch (PDOException $e) {
    respond([
        'success' => false,
        'message' => 'Er is een databasefout opgetreden.',
    ]);
}
