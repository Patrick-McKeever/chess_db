CREATE DATABASE IF NOT EXISTS chess;
USE chess;

/* The entire SQL file contains about 40,000,000 INSERTS. */
/* Obviously, this is impractical to upload, so I've just */
/* included the CREATE statements for the purposes of */
/* this demo. */
CREATE TABLE PLAYERS(
				player_id INTEGER PRIMARY KEY,
				name VARCHAR(80)
			);
CREATE TABLE EVENTS(
				event_id INTEGER PRIMARY KEY,
				name VARCHAR(100)
			);
CREATE TABLE GAMES(
				game_id INTEGER PRIMARY KEY,
				white_player_id INTEGER,
				black_player_id INTEGER,
				event_id INTEGER,
				outcome VARCHAR(10),
				date VARCHAR(20),
				FOREIGN KEY(white_player_id) REFERENCES PLAYERS(player_id),
				FOREIGN KEY(black_player_id) REFERENCES PLAYERS(player_id),
				FOREIGN KEY(event_id) REFERENCES EVENTS(event_id)
			);
CREATE TABLE MOVES(
				game_id INTEGER,
				turn_no INTEGER,
				white_to_move INTEGER,
				san_str VARCHAR(10),
				fen_before VARCHAR(100),
				PRIMARY KEY(game_id, turn_no, white_to_move),
				FOREIGN KEY(game_id) REFERENCES GAMES(game_id)
			);
CREATE TABLE RATINGS(
				game_id INTEGER,
				player_id INTEGER,
				elo INTEGER,
				PRIMARY KEY(game_id, player_id),
				FOREIGN KEY(game_id) REFERENCES GAMES(game_id),
				FOREIGN KEY(player_id) REFERENCES PLAYERS(player_id)
			);
CREATE INDEX fen_before ON MOVES(fen_before);
CREATE INDEX outcome ON GAMES(outcome);
