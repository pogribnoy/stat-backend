//http://admin.rashodygoroda.org/js/chart.js
var app = {};

(function (app) {
	var _items = null;
	var chartEl = document.querySelector('svg');
	var legendEl = document.querySelector('#legend');
	var cumulativePercent = 0;
	
	var colors = [
		{hex:'#78281F'}, {hex:'#512E5F'}, {hex:'#1B4F72'}, {hex:'#0E6251'}, {hex:'#186A3B'}, {hex:'#7D6608'}, {hex:'#784212'}, {hex:'#7B7D7D'}, {hex:'#4D5656'}, {hex:'#1B2631'}, {hex:'#C0392B'}, {hex:'#8E44AD'}, {hex:'#3498DB'}, {hex:'#17A589'}, {hex:'#27AE60'}, {hex:'#D4AC0D'}, {hex:'#E67E22'}, {hex:'#BDC3C7'}, {hex:'#5D6D7E'}, {hex:'#ABB2B9'}, {hex:'#E6B0AA'}, {hex:'#D7BDE2'}, {hex:'#A9CCE3'}, {hex:'#A2D9CE'}, {hex:'#ABEBC6'}, {hex:'#F9E79F'}, {hex:'#EDBB99'}, {hex:'#A04000'},
	];
	
	function isDarkColor(color) {
		var match = /rgb\((\d+).*?(\d+).*?(\d+)\)/.exec(color);
		return ( match[1] & 255 ) + ( match[2] & 255 ) + ( match[3] & 255 ) < 3 * 256 / 2;
	}
	
	function hexToRgb(hex) {
		// Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
		var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
		hex = hex.replace(shorthandRegex, function(m, r, g, b) {
			return r + r + g + g + b + b;
		});

		var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
		return result ? {
			r: parseInt(result[1], 16),
			g: parseInt(result[2], 16),
			b: parseInt(result[3], 16)
		} : null;
	}
	
	function getCoordinatesForPercent(percent) {
		var x = Math.cos(2 * Math.PI * percent);
		var y = Math.sin(2 * Math.PI * percent);
		return { x:x, y:y };
	}
	
	function addSVGCSSClassFix(element, cssClass) {
		var currentClassStr = '';
		if(element.hasAttribute('class')) currentClassStr = element.getAttribute('class');
		
		var CSSClasses = cssClass.split(" ");
		var CSSClassesLength = CSSClasses.length;
		for(var i = 0; i < CSSClassesLength; i++) {
    	//null.indexOf('');
			if(currentClassStr.indexOf(CSSClasses[i]) == -1) currentClassStr == '' ? currentClassStr += CSSClasses[i] : currentClassStr += ' ' + CSSClasses[i];
		}
		// удаления класса при его наличии: currentClassStr.replace(/active/g, '')
		element.setAttribute('class', currentClassStr);
		
		//console.log(currentClassStr);
		//console.log(element.getAttribute('class'));
	}
	
	function deleteSVGCSSClassFix(element, cssClass) {
		var currentClassStr = '';
		if(element.hasAttribute('class')) currentClassStr = element.getAttribute('class');
		
		var CSSClasses = cssClass.split(" ");
		var CSSClassesLength = CSSClasses.length;
		for(var i = 0; i < CSSClassesLength; i++) {
			var regexp = new RegExp('\\s' + CSSClasses[i], "g");
			currentClassStr = currentClassStr.replace(regexp, '');
      //currentClassStr.replace(null, null);
		}
		element.setAttribute('class', currentClassStr);
		
		//console.log(currentClassStr);
		//console.log(element.getAttribute('class'));
	}
	
	function addCSSClassFix(element, cssClass) {
		var currentClassStr = '';
		if(element.classList) currentClassStr = element.classList;
		else currentClassStr = element.className;
			
		var CSSClasses = cssClass.split(" ");
		var CSSClassesLength = CSSClasses.length;
		for(var i = 0; i < CSSClassesLength; i++) {
			if(element.classList) element.classList.add(CSSClasses[i]);
			else if(!currentClassStr.indexOf(CSSClasses[i]) !== -1)	element.className += ' ' +	CSSClasses[i];
		}
	}
	
	function getRandomColor() {
		var colorsLength = colors.length;
		var randomID = Math.floor(Math.random() * (colorsLength));
		return colors[randomID].hex;
	}
	
	function itemMouseOver(item) {
		var chartItem = document.getElementById(item.chart.pathID);
		var legendItemText = item.legend.legendItemText;
		addSVGCSSClassFix(chartItem, 'highlight');
		addCSSClassFix(legendItemText, 'danger');
		console.log(item.chart);
		item.chart.selection.pathEl.removeAttribute('hidden');
	}
	
	function itemMouseOut(item) {
		var chartItem = document.getElementById(item.chart.pathID);
		var legendItemText = item.legend.legendItemText;
		if(chartItem.classList) chartItem.classList.remove('highlight');
		else deleteSVGCSSClassFix(chartItem, 'highlight');
		if(legendItemText.classList) legendItemText.classList.remove("danger");
		else deleteSVGCSSClassFix(chartItem, 'danger');
		item.chart.selection.pathEl.setAttribute('hidden', 'true');
	}
	
	function drawLegendItem(item) {
		var legendItemText = document.createElement('span');
		legendItemText.innerHTML = '<div class="pull-left rounded-corner" style="background:' + item.chart.color + '">&nbsp;</div>&nbsp;' + item.text;

		var legendItemButton = document.createElement('button');
		legendItemButton.setAttribute('type', "button");
		legendItemButton.setAttribute('title', "Нажмите для перехода к списку");
		legendItemButton.setAttribute('aria-label', "Нажмите для перехода к списку");
		legendItemButton.classList.add("btn", "btn-default", "btn-xs");
		legendItemButton.innerHTML = '<span class="glyphicon glyphicon-option-horizontal" aria-hidden="true"></span>';
		legendItemButton.addEventListener("click", function() { alert('document.location = "' + item.url + '";'); });
		
		var legendItemContainer = document.createElement('p');
		var legendItem = document.createElement('span');
		legendItem.addEventListener("mouseover", function() { itemMouseOver.bind(this)(item); });
		legendItem.addEventListener("mouseout", function() { itemMouseOut.bind(this)(item); });
		legendItem.appendChild(legendItemText);
		legendItem.appendChild(legendItemButton);
		legendItemContainer.appendChild(legendItem);
		legendEl.appendChild(legendItemContainer);
		item.legend = {
			legendItemText: legendItemText,
			legendItemButton: legendItemButton,
		}
	}
	
	function drawChartItemSelection(item) {
		item.chart.selection = {
			color: '#666',
		};
		var width = 0;
		item.chart.selection.startX = item.chart.startX + width;
		item.chart.selection.startY = item.chart.startY + width;
		item.chart.selection.endX = item.chart.endX + width;
		item.chart.selection.endY = item.chart.endY + width;
    
		width = 1.05;
		var newx1 = item.chart.startX*width;
		var newy1 = item.chart.startY*width;
		var newx2 = item.chart.endX*width;
		var newy2 = item.chart.endY*width;

		// if the slice is more than 50%, take the large arc (the long way around)
		var largeArcFlag = item.percent > .5 ? 1 : 0;
	
		// create an array and join it just for code readability
		var pathData = 'M ' + item.chart.startX + ' ' + item.chart.startY + // Move
			' A 1 1 0 ' + largeArcFlag + ' 1 ' + item.chart.selection.endX + ' ' + item.chart.selection.endY +  // Arc 1
			' L ' + newx2 + ' ' + newy2 + // Line 1
			' A 1.05 1.05 0 ' + largeArcFlag + ' 0 ' + newx1 + ' ' + newy1 + // Arc 2
			' L ' + item.chart.startX + ' ' + item.chart.startY + // Line 2
			'';

		// create a <path> and append it to the <svg> element
		var pathEl = document.createElementNS('http://www.w3.org/2000/svg', 'path');
		pathEl.setAttribute('d', pathData);
		//pathEl.setAttribute('id', item.chart.pathID);
		pathEl.setAttribute('fill', item.chart.color);
		pathEl.setAttribute('hidden', 'true');
		addSVGCSSClassFix(pathEl, 'chart-item-selection');
		
		item.chart.selection.pathEl = pathEl;
		var chartItem = document.getElementById(item.chart.pathID);
		chartItem.parentNode.appendChild(item.chart.selection.pathEl);
	}

	function drawChartItem(item) {
		item.chart = {
			color: getRandomColor(),
			pathID: Math.random(),
		};
	
		var coords;
		// destructuring assignment sets the two variables at once
		coords = getCoordinatesForPercent(cumulativePercent);
		item.chart.startX = coords.x;
		item.chart.startY = coords.y;

		// each slice starts where the last slice ended, so keep a cumulative percent
		cumulativePercent += item.percent;

		coords = getCoordinatesForPercent(cumulativePercent);
		item.chart.endX = coords.x;
		item.chart.endY = coords.y;

		// if the slice is more than 50%, take the large arc (the long way around)
		var largeArcFlag = item.percent > .5 ? 1 : 0;

		// create an array and join it just for code readability
		var pathData = 'M 0 0 ' + // Move
			'L ' + item.chart.startX + ' ' + item.chart.startY + // Line
			' A 1 1 0 ' + largeArcFlag + ' 1 ' + item.chart.endX + ' ' + item.chart.endY + // Arc
			' L 0 0'; // Line

		// create a <path> and append it to the <svg> element
		var pathEl = document.createElementNS('http://www.w3.org/2000/svg', 'path');
		pathEl.setAttribute('d', pathData);
		pathEl.setAttribute('id', item.chart.pathID);
		pathEl.setAttribute('fill', item.chart.color);
		//pathEl.classList.add('chart-item'); // так не работает с SVG
		addSVGCSSClassFix(pathEl, 'chart-item');
		/*pathEl.onclick = function() {
			alert('document.location = "' + item.url + '";');
		};*/
		
		var pathElTitle = document.createElementNS('http://www.w3.org/2000/svg', 'title');
		var textNode = document.createTextNode(item.text);
		pathElTitle.appendChild(textNode);
		pathEl.appendChild(pathElTitle);
		
		pathEl.addEventListener("mouseover", function() { itemMouseOver.bind(this)(item); });
		pathEl.addEventListener("mouseout", function() { itemMouseOut.bind(this)(item); });
		pathEl.addEventListener("click", function() { alert('document.location = "' + item.url + '";'); });
		
		chartEl.appendChild(pathEl);
	}
	
	function drawChartItemText(item) {
		// ищем вектор к центру треугольника (биссектриса)
		var cx = (item.chart.startX + item.chart.endX);
		var cy = (item.chart.startY + item.chart.endY);
		// находим длину и смотрим, во сколько раз она меньше 1
		var modulV = Math.sqrt(cx*cx + cy*cy);
		var k = 0.75 / modulV;
		// умножаем координаты в это количество раз
		var tx = cx * k;
		//var fSize = 0.2;
		//tx = tx - ((parseFloat(item.percent).toFixed(2)) + '%').length * fSize / 10;
		var ty = cy * k;
		
		var textEl = document.createElementNS('http://www.w3.org/2000/svg', 'text');
		textEl.setAttribute('x', tx + 'px');
		textEl.setAttribute('y', ty + 'px');
		//textEl.setAttribute('title', item.text);
		console.log(hexToRgb(item.chart.color));
		//rgbColor = hexToRgb(item.chart.color);
		//textEl.setAttribute('fill', isDarkColor('rgb(' + rgbColor.r + ',' + rgbColor.g + ',' + rgbColor.b + ')') ? 'white' : 'black');
		var textElTitle = document.createElementNS('http://www.w3.org/2000/svg', 'title');
		var textNode = document.createTextNode(item.text);
		textElTitle.appendChild(textNode);
		textEl.appendChild(textElTitle);
		
		textNode = document.createTextNode(parseFloat(item.percent).toFixed(2) + '%');
		textEl.appendChild(textNode);		
		
		textEl.addEventListener("mouseover", function() { itemMouseOver.bind(this)(item); });
		textEl.addEventListener("mouseout", function() { itemMouseOut.bind(this)(item); });
		textEl.addEventListener("click", function() { alert('document.location = "' + item.url + '";'); });
		chartEl.appendChild(textEl);
	}
	
	function draw(items, options) {
		//console.log(_items);
		if(options) {
			if(options.chartSelector) chartEl = document.querySelector(options.chartSelector);
			if(options.legendSelector) legendEl = document.querySelector(options.legendSelector);
			if(options.minimalPercentForText) minimalPercentForText = options.minimalPercentForText;
			else minimalPercentForText = 0.05;
		}
		
		if(_items) {
			chartEl.innerHTML = '';
			legendEl.innerHTML = '';
			cumulativePercent = 0;
		}
		else _items = items;
		
		var itemsLength = items.length;
		for(var i=0; i<itemsLength; i++) {
			drawChartItem(items[i]);
			drawLegendItem(items[i]);
			drawChartItemSelection(items[i]);
		}
		for(var i=0; i<itemsLength; i++) {
			if(items[i].percent > minimalPercentForText) drawChartItemText(items[i]);
		}
		return this;
	}
	
	function hide() {
		if(_items) {
			while (chartEl.firstChild) {
				chartEl.removeChild(chartEl.firstChild);
			}
			while (legendEl.firstChild) {
				legendEl.removeChild(legendEl.firstChild);
			}
			cumulativePercent = 0;
			_items = null;
		}
	}
	
	app.chart = {
		draw: draw,
		hide: hide,
	};
}(app));

function generateItems() {
	var id = 0;
	var prcnt = 1;
	var items = [];
	do {
		var rnd = Math.random() * 0.4;
		var url = '/expenselist?filter_organization=' + id;
		if(prcnt < rnd) {
			rnd = prcnt;
			prcnt = 0;
		}
		else prcnt -= rnd;
		
		var item = { percent: rnd, text: 'Item ' + parseFloat(rnd).toFixed(2) + '%', url: url };
		items.push(item);

		id++;
	}
	while(prcnt>0);
	return items;
};


var items = generateItems();
var items2 = generateItems();

console.log(items);

app.chart.draw(items, {chartSelector: 'svg', legendSelector: '#legend1', minimalPercentForText: 0.1});

//var tmp = app.chart.draw(items2, {chartSelector: '#chart2', legendSelector: '#legend2'});

function redraw(){
	//console.log(items2);
	tmp.draw(items2, {chartSelector: '#chart2', legendSelector: '#legend2'})
}

function hide(){
	//console.log(items2);
	tmp.hide();
}
