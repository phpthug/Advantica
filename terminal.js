var Terminal = function(rows, cols) {

	/**
	* Initialize our terminal
	*/
	this.terminal = [];
	this.rows = rows;
	this.cols = cols;
	this.cursorPosition = new Position();
	
	this.keyStack = [];
	this.output = '';

	//Some instance variables
	var that = this;
	var inputMode = 'overwrite';

	for(var i = 0; i < this.rows; i++) {
		this.terminal[i] = [];
		for(var k = 0; k < this.cols; k++) {
			this.terminal[i][k] = '';
		}
	}

	this.elTerminal = document.createElement('div');

	this.moveCursor = function(row, col) {
		if(row < this.rows && row >= 0) {
			this.cursorPosition.setRow(row);
		}

		if(col < this.cols && col >= 0) {
			this.cursorPosition.setCol(col);
		}

		writeToScreen();

	};

	var writeString = function(str) {
		for(var l in str) {
			that.terminal[that.cursorPosition.row][that.cursorPosition.col] = str[l];
			if(that.cursorPosition.col + 1 < that.cols) {
				that.moveCursor(that.cursorPosition.row, that.cursorPosition.col + 1);
			}
		}
	};

	this.writeToTerminal = function(str) {

		if(inputMode == 'overwrite') {
			writeString(str);
		} else if(inputMode == 'insert') {
			var shiftCharStartingFrom = this.cursorPosition.col;
			var finalCol = this.cursorPosition.col + str.length;
			var tmpString = '';

			//Grab the string that needs to be shifted
			for(var i = this.cursorPosition.col; i < this.cols; i++) {
				tmpString += this.terminal[this.cursorPosition.row][i];
			}

			//Write our new string
			writeString(str);

			//Write the original string, shifted
			writeString(tmpString);

			this.cursorPosition.col = finalCol + 1 < this.cols ? finalCol : this.cols - 1;
		}

		writeToScreen();
	};

	this.clearTerminal = function() {
		for(var i = 0; i < this.rows; i++) {
			for(var k = 0; k < this.cols; k++) {
				this.terminal[i][k] = '';
			}
		}

		writeToScreen();
	}

	var writeToScreen = function() {
		var output = '';

		for(var i = 0; i < that.rows; i++) {
			for(var k = 0; k < that.cols; k++) {
				if(that.cursorPosition.row == i && that.cursorPosition.col == k) {
					output += '<span style="color: red;font-weight: bold;text-decoration: underline;">';
					output += that.terminal[i][k] != '' ? that.terminal[i][k] : '_';
					output += '</span>';
				} else {
					output += that.terminal[i][k] != '' ? that.terminal[i][k] : '_';
				}
			}
			output += "<br />";
		}

		document.body.innerHTML = output;
	};

	this.updateInputMode = function(mode) {
		inputMode = mode;
	};

	this.processKeyStack = function() {
		var command = this.keyStack.pop();

		switch(command) {
			case 68:
				this.moveCursor(this.cursorPosition.row + 1, this.cursorPosition.col);
				break;
			case 85:
				this.moveCursor(this.cursorPosition.row - 1, this.cursorPosition.col);
				break;
			case 76:
				this.moveCursor(this.cursorPosition.row, this.cursorPosition.col - 1);
				break;
			case 82:
				this.moveCursor(this.cursorPosition.row, this.cursorPosition.col + 1);
				break;
			case 72:
				this.moveCursor(0, 0);
				break;
			case 67:
				this.clearTerminal();
				break;
			case 66:
				this.moveCursor(this.cursorPosition.row, 0);
				break;
			case 73: 
				this.updateInputMode('insert');
				break;
			case 79:
				this.updateInputMode('overwrite');
				break;
		}

		this.keyStack = [];
	};

	document.onkeyup = function(e) {
		if (e.shiftKey && e.which == 54) {
			that.keyStack.push(e);
			//Handle more than one circumflex in pairs of two
				
		}

		//Listen for the next character
		if(that.keyStack.length && e.which >= 48 && e.which <= 57 && ! e.shiftKey) {
			that.keyStack.push(e.which);
			if(that.keyStack.length == 3) {
				var col = that.keyStack.pop();
				var row = that.keyStack.pop();
				that.moveCursor(row - 48, col - 48);
				that.keyStack = [];
			}
		} else if(that.keyStack.length == 1 && e.which != 54 && e.which != 16 && ! e.shiftKey) {	
			that.keyStack.push(e.which);
			that.processKeyStack();
		} else if (! that.keyStack.length && e.which != 54 && e.which != 16 && ! e.shiftKey) {
			that.writeToTerminal(String.fromCharCode(e.which));
		}
	};

	writeToScreen();

};

var Position = function() {
	this.row = 0;
	this.col = 0;

	this.setRow = function(row) {
		this.row = row;
	};

	this.setCol = function(col) {
		this.col = col;
	};
};