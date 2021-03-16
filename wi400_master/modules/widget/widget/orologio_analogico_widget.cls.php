<?php
class OROLOGIO_ANALOGICO_WIDGET extends wi400Widget {
	private $result = "SUCCESS";

	function __construct($progressivo) {
		$this->progressivo = $progressivo;
		$this->parameters['TITLE'] = "OROLOGIO";
		$this->parameters['INTERVAL'] = 'AUTO';
		$this->parameters['MINIMIZED'] = true;
	}

	public function getHtmlBody() {
		$id_canvas = "canvas_".$this->progressivo;
		$html = '<center><canvas id="'.$id_canvas.'" width="200" height="200"></canvas></center>

				<script>
				    var canvas_'.$this->progressivo.' = document.getElementById("'.$id_canvas.'");
				    var ctx = canvas_'.$this->progressivo.'.getContext("2d");
				    var radius = canvas_'.$this->progressivo.'.height / 2;
				    ctx.translate(radius, radius);
				    radius *= 0.90;
				    setInterval(drawClock, 1000);
				    $color = "#575757";
				
				    function drawClock() {
				        drawFace(ctx, radius);
				        drawNumbers(ctx, radius);
				        drawTime(ctx, radius);
				    }
				
				    function drawFace(ctx, radius) {
				        ctx.beginPath();
				        ctx.arc(0, 0, radius, 0, 2 * Math.PI);
				        ctx.fillStyle = "white";
				        ctx.fill();
				
				        var gradient;
				        gradient = ctx.createRadialGradient(0, 0, radius * 0.95, 0, 0, radius * 1.05);
				        gradient.addColorStop(0, $color);
				        gradient.addColorStop(1, "white");
				        ctx.strokeStyle = gradient;
				        ctx.lineWidth = radius * 0.1;
				        ctx.stroke();
				
				        ctx.beginPath();
				        ctx.arc(0, 0, radius * 0.05, 0, 2 * Math.PI);
				        ctx.fillStyle = $color;
				        ctx.fill();
				
				        ctx.textBaseline = "middle";
				        ctx.textAlign = "center";
				        ctx.font = "bold " + radius * 0.12 + "px courier";
				        ctx.fillText("WI400", 0, 30);
				    }
				    function drawNumbers(ctx, radius) {
				
				        var ang, num, w, h;
				
				        for (num = 1; num < 61; num++) {
				            ang = num * Math.PI / 30;
				            ctx.rotate(ang);
				            ctx.translate(0, -radius * 0.85);
				            if (num % 15 == 0) {
				                w = 7;
				                h = 4;
				            } else if (num % 5 == 0) {
				                w = 5;
				                h = 2;
				            } else {
				                w = 2;
				                h = 0.8;
				            }
				            ctx.beginPath();
				            ctx.rect(0, 0, h, w);
				            ctx.fillStyle = $color;
				            ctx.fill();
				            ctx.translate(0, radius * 0.85);
				            ctx.rotate(-ang);
				        }
				    }
				    function drawTime(ctx, radius) {
				        var now = new Date();
				        var hour = now.getHours();
				        var minute = now.getMinutes();
				        var second = now.getSeconds();
				        //hour
				        hour = hour % 12;
				        hour = (hour * Math.PI / 6) + (minute * Math.PI / (6 * 60)) + (second * Math.PI / (360 * 60));
				        drawHand(ctx, hour, radius * 0.5, radius * 0.04);
				        //minute
				        minute = (minute * Math.PI / 30) + (second * Math.PI / (30 * 60));
				        drawHand(ctx, minute, radius * 0.75, radius * 0.04);
				        // second
				        second = (second * Math.PI / 30);
				        drawHand(ctx, second, radius * 0.85, radius * 0.02);
				    }
				
				    function drawHand(ctx, pos, length, width) {
				        ctx.beginPath();
				        ctx.strokeStyle = $color;
				        ctx.lineWidth = width;
				        ctx.lineCap = "round";
				        ctx.moveTo(0, 0);
				        ctx.rotate(pos);
				        ctx.lineTo(0, -length);
				        ctx.stroke();
				        ctx.rotate(-pos);
				    }
				</script>';

		return $html;
	}

	function run() {
		$this->parameters['TITLE'] = "OROLOGIO";
		$this->parameters['BODY'] = array();

		return $this->result;
	}
}
