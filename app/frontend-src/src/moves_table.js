import React, { Component } from 'react';
import { Chessboard } from 'react-chessboard';
import Chess from 'chess.js';

class MovesTable extends Component {
	constructor(props) {
		super(props);
		this.state = {
			fen: props.fen,
			top_moves: [],
			loading: true
		}

	}

	componentDidMount() {
		this.GetTopMoves();
	}

	componentDidUpdate() {
		if(this.state.fen != this.props.fen) {
			this.setState({ fen: this.props.fen, loading: true }, this.GetTopMoves);
		}
	}


	GetTopMoves() {
		// Fetch data from the API
		fetch('/get_moves.php?fen_str=' + this.state.fen,
			{
				method: 'GET',
				headers: {
					'Content-Type': 'application/json'
				},
				credentials: 'include',
			})
			.then(response => {
				if (!response.ok) {
					throw new Error('Network response was not ok');
				}
				return response.json();
			})
			.then(data => {
				this.setState({ top_moves: data, loading: false });	
			})
			.catch(error => {
				this.setState({ error, loading: false });
			});
	}


	render() {
		if(this.state.loading) {
			return <p>Loading...</p>;
		}

		if(this.state.top_moves.length == 0) {
			return <p>No games from this position</p>;
		}

		return (<table>
					<thead>
					<tr>
						<td>Move</td>
						<td>Occurrences</td>
						<td>White Wins</td>
						<td>Black Wins</td>
						<td>Draws</td>
					</tr>
					</thead>
					<tbody>{
						this.state.top_moves.map(move => {
							return <tr>
								<td>{move["san_str"]}</td>
								<td>{move["occurrences"]}</td>
								<td>{Math.round((move["wwin"]  * 100) / move["occurrences"])}%</td>
								<td>{Math.round((move["bwin"]  * 100) / move["occurrences"])}%</td>
								<td>{Math.round((move["draws"] * 100) / move["occurrences"])}%</td>
							</tr>
						})
					}</tbody>
				</table>);
	}
}

export default MovesTable;
