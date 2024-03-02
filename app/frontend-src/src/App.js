import './App.css';
import './all.css';

import React, { Component } from 'react';
import { Tabs, TabLink, TabContent } from 'react-tabs-redux';
import { Chessboard } from 'react-chessboard';
import Chess from 'chess.js';
import ChessGame from './chess_game';
import OutcomeGraph from './outcome_graph';
import GamesList from './games_list';
import MovesTable from './moves_table';

class App extends Component {
	constructor(props) {
		super(props);
		const game = new Chess();
		this.state = {
			fen: "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1",
			game: game,
			top_moves: [],
			outcomes: [],
			loading: true,
			error: null,
			current_game: null,
			tab: "search"
		};
	}

	componentDidMount() {
		document.title = "Chess DB";
	}

	sanitizeFen(raw_fen) {
		let fields = raw_fen.split(' ');
		fields[3] = "-";
		return fields.join(' ');
	}


	onDrop(src, target) {
		const game_copy = {...this.state.game};
		//let pgn = this.state.game.pgn();
		let pgn="";
		const res = game_copy.move({
			from: src,
			to: target,
			promotion: "q"
		});

		let san_fen = this.sanitizeFen(game_copy.fen());
		this.setState({ game: game_copy, fen: san_fen });

		if(res === null) {
			return false;
		}

		return true;
	}

	SetCurrentGame(game_id) {
		console.log("Setting game to " + game_id);
		console.log(game_id);
		this.setState({ current_game: game_id });
		this.setState({ tab: "game_view" });
	}


	render() {
		const { data, loading, error, move_ind } = this.state;

		return (
			<Tabs selectedTab={this.state.tab}>
				<TabLink to="search">Search</TabLink>
				<TabLink to="game_view">View Game</TabLink>

				<TabContent for="search">
					<div>
						{/* Render your component using the fetched data */}

						<div className="left-side-panel">
							<Chessboard id="board1" boardWidth={560} position={this.state.fen} onPieceDrop={this.onDrop.bind(this)}/>
						</div>
						
						<div className="side-panel">
							<Tabs>
								<TabLink to="top_moves">Top Moves</TabLink>
								<TabLink to="games">Games</TabLink>
								<TabLink to="outcomes">Outcomes by ELO</TabLink>

								<TabContent for="top_moves">
									<MovesTable fen={this.state.fen}/>
								</TabContent>
								<TabContent for="games">
									<GamesList fen={this.state.fen} set_game={this.SetCurrentGame.bind(this)}/>
								</TabContent>
								<TabContent for="outcomes">
									<OutcomeGraph fen={this.state.fen} />
								</TabContent>
							</Tabs>
						</div>

					</div>
				</TabContent>

				<TabContent for="game_view">
					<ChessGame game_id={this.state.current_game}/>
				</TabContent>
			</Tabs>
		);
	}
}

export default App;
