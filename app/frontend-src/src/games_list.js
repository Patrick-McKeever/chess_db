import React, { Component } from 'react';
import { Chessboard } from 'react-chessboard';
import Chess from 'chess.js';
import GameSearch from './game_search';

class GamesList extends Component {
	constructor(props) {
		super(props);
		this.state = {
			fen: props.fen,
			set_parent_game: props.set_game,
			games: [],
			loading: true
		}

	}

	componentDidMount() {
		this.GetGames();
	}

	componentDidUpdate() {
		if(this.state.fen != this.props.fen) {
			this.setState({ fen: this.props.fen, loading: true }, this.GetGames);
		}
	}

	GetGames(
			white_elo_min = null,
			white_elo_max = null,
			black_elo_min = null,
			black_elo_max = null,
			white_name = null,
			black_name = null,
			result = "Any"
	) {
		// Fetch data from the API
		let url = '/get_games.php?fen_str=' + this.state.fen;

		if(white_elo_min != null) {
			url += '&wmin=' + String(white_elo_min);
		}
		if(white_elo_max != null) {
			url += '&wmax=' + String(white_elo_max);
		}
		if(black_elo_min != null) {
			url += '&bmin=' + String(black_elo_min);
		}
		if(black_elo_max != null) {
			url += '&bmax=' + String(black_elo_max);
		}
		if(white_name != null) {
			url += '&wname=' + String(white_name);
		}
		if(black_name != null) {
			url += '&bname=' + String(black_name);
		}
		if(result != "Any") {
			url += '&result=' + String(result);
		}

		fetch(url,
			{
				method: 'GET',
				headers: {
					'Content-Type': 'application/json'
				},
				credentials: 'include',
			})
			.then(response => {
				if (!response.ok) {
					console.log(response);
					throw new Error('Network response was not ok');
				}
				return response.json();
			})
			.then(data => {
				this.setState({ games: data, loading: false });
			})
			.catch(error => {
				this.setState({ error, loading: false });
			});
	}

	render() {
		if(this.state.loading) {
			return <p>Loading...</p>;
		}

		if(this.state.games.length == 0) {
			return <p>No games from this position</p>;
		}

		return (
			<div>
			<GameSearch submit_handler={this.GetGames.bind(this)}/>
			<table>
				<thead>
				<tr>
					<td>White</td>
					<td>Black</td>
					<td>Date</td>
					<td>Outcome</td>
				</tr>
				</thead>
				<tbody>{
					this.state.games.map(game => {
						return <tr  className={"selectable"}
								key={game["id"]} onClick={() => this.state.set_parent_game(game["id"])}>
							<td>{game["w_player"]} ({game["w_elo"]})</td>
							<td>{game["b_player"]} ({game["b_elo"]})</td>
							<td>{game["date"]}</td>
							<td>{game["outcome"]}</td>
						</tr>
					})
				}</tbody>
			</table>
			</div>
		);
	}
}

export default GamesList;
