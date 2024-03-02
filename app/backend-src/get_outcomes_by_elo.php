<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$servername = "db";
$username = "chess_user";
$password = "mysql_password";
$db = "chess";

$conn = new mysqli($servername, $username, $password, $db);
if ($conn->connect_error) {
    die("Connection failed " . $conn->connect_error);
}

// Because start position is a lot harder to query than others 
// (exponential reduction in no. of games to search with each passing move)
// we simply encode this data. If the database changes, then be sure to edit 
// this.
$START_FEN_STR = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';
$START_POSITION_RESULTS = '[
    {
        "elor": 2800,
        "occs": 788,
        "wwin": 208,
        "bwin": 168,
        "draw": 412
    },
    {
        "elor": 2600,
        "occs": 129476,
        "wwin": 39285,
        "bwin": 25933,
        "draw": 64258
    },
    {
        "elor": 2400,
        "occs": 72736,
        "wwin": 22386,
        "bwin": 15184,
        "draw": 35166
    },
    {
        "elor": 2200,
        "occs": 3744,
        "wwin": 1387,
        "bwin": 1090,
        "draw": 1267
    },
    {
        "elor": 2000,
        "occs": 175,
        "wwin": 67,
        "bwin": 52,
        "draw": 56
    }
]';

if($_GET["fen_str"] == $START_FEN_STR) {
    echo($START_POSITION_RESULTS);
} else {
    $outcomes_query = $conn->prepare(
                    "SELECT (FLOOR(w.elo / 200) * 200) AS elor,
                        COUNT(*) AS occs,
                        COUNT(CASE WHEN outcome = '1-0' THEN 1 END) AS wwin,
                        COUNT(CASE WHEN outcome = '0-1' THEN 1 END) AS bwin,
                        COUNT(CASE WHEN outcome = '1/2-1/2' THEN 1 END) AS draw
                    FROM MOVES
                    JOIN GAMES ON MOVES.game_id = GAMES.game_id
                    JOIN RATINGS w ON white_player_id = w.player_id 
                        AND GAMES.game_id = w.game_id
                    JOIN RATINGS b ON black_player_id = b.player_id 
                        AND b.game_id = GAMES.game_id
                    WHERE fen_before = ?
                        AND NOT w.elo = ''
                        AND NOT outcome = '*'
                        AND FLOOR((w.elo / 200) * 200) >= 2000
                        AND b.elo > (FLOOR(w.elo / 200) * 200)
                        AND b.elo < (FLOOR(w.elo / 200) * 200) + 200
                        AND w.elo < (FLOOR(w.elo / 200) * 200) + 200
                    GROUP BY (FLOOR(w.elo / 200) * 200)
                    ORDER BY (FLOOR(w.elo / 200) * 200) DESC;");
    
    $resp = array();
    if ($outcomes_query->bind_param('s', $_GET['fen_str'])) {
        $outcomes_query->execute();
        $outcomes_result = $outcomes_query->get_result();
        while($outcome = $outcomes_result->fetch_assoc()) {
            $resp[] = $outcome;
        }
    } else {
        die("Outcomes query failed.");
    }
    
    echo(json_encode($resp));
}

?>