import React, { Component } from 'react';
import { Chessboard } from 'react-chessboard';
import Chess from 'chess.js';
import { VictoryStack, VictoryLegend, VictoryBar, VictoryAxis, VictoryChart } from 'victory';

class OutcomeGraph extends Component {
	constructor(props) {
		super(props);
		this.state = {
			fen: props.fen,
			loading: true,
			outcomes: []
		}

	}

	componentDidMount() {
		this.GetOutcomes();
	}

	componentDidUpdate() {
		if(this.state.fen != this.props.fen) {
			this.setState({ fen: this.props.fen, loading: true }, this.GetOutcomes);
		}
	}

	GetOutcomes() {
		// Fetch data from the API
		fetch('/get_outcomes_by_elo.php?fen_str=' + this.state.fen,
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
				this.setState({ outcomes: data, loading: false });
			})
			.catch(error => {
				this.setState({ error, loading: false });
			});
	}


	render() {
		if(this.state.loading) {
			return <p>Loading...</p>;
		}

		if(this.state.outcomes.length == 0) {
			return <p>No games from this position</p>;
		}

		return (<div>
					<VictoryChart domainPadding={10} padding={{ top: 5, bottom: 50, left: 50, right: 50 }} width={300} height={250}>
						<VictoryAxis 
							label="ELO range"
							tickValues={this.state.outcomes.map((el) => {
								return el["elor"]
							})}
							style={{
								tickLabels: { fontSize: 10, padding: 5, angle: -45, textAnchor: 'end' }
							}}
						/>
						<VictoryAxis 
							dependentAxis
							label="Percentage Outcome"
							tickValues={[0.25, 0.50, 0.75, 1]}
							style={{
								tickLabels: { fontSize: 10, padding: 5, angle: -45, textAnchor: 'end' }
							}}
						/>
						<VictoryStack colorScale={["#baffc9", "#bae1ff", "#ffb3ba"]}>
							<VictoryBar key={"wwin"} data={
								this.state.outcomes.map(el => { return { x : String(el["elor"]), y : el["wwin"] / el["occs"] }; })
							}/>
							<VictoryBar key={"draw"} data={
								this.state.outcomes.map(el => { return { x : String(el["elor"]), y : el["draw"] / el["occs"] }; })
							}/>
							<VictoryBar key={"bwin"} data={
								this.state.outcomes.map(el => { return { x : String(el["elor"]), y : el["bwin"] / el["occs"] }; })
							}/>
						</VictoryStack>
					</VictoryChart>


					<VictoryLegend data={[
						{ name: 'White Wins', symbol: { fill: "#baffc9" } },
						{ name: 'Draw', symbol: { fill: "#bae1ff" } },
						{ name: 'Black Wins', symbol: { fill: "#ffb3ba" } },
						]}
						orientation="horizontal"
						gutter={10}
						style={{ 
							title: { fontSize: 11 },
							labels: { lineHeight: 2, padding: 5 },
							parent: { display: 'flex', justifyContent: 'center' },
						}}/>
				</div>);
	}
}

export default OutcomeGraph;
