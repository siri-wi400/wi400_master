	

	var dragObj = "", dragStart = 0, dragStartExternal = 0, provaEvent = "", oldProva = "", creaCalendario, alertShortbooking=false, eventBooking;
	var slotTipoOperazione;
	var removeDropEvent, noClickPrenot = false;
	/*var d = new Date();
	var month = d.getMonth()+1;
	var day = d.getDate();
	month = (month<10 ? '0' : '') + month;
	day = (day<10 ? '0' : '') + day;*/
	
	//console.log(data, societa, sito, deposito, fornitore);
	var calendInterval = [];
	
	if(typeof(eventTime) != "undefined") {
		var diffTime = eventTime - currentTime;
		var dur = moment.duration(diffTime*1000, 'milliseconds');
		var intervalTime = 1000;
		var timeInterval = "";
		var flagColor = 0;

		timeInterval = setInterval(function(){
			dur = moment.duration(dur - intervalTime, 'milliseconds');
			var millisec = dur._milliseconds;
			//console.log(millisec);
			var text = moment.utc(millisec).format("HH:mm:ss");
		    $('.countdown').text(text);
		    if(!flagColor && millisec < 60000) {
		    	$('.countdown').css("color", "red");
		    	flagColor = 1;
		    }
		    if(millisec <= 0) {
		    	clearInterval(timeInterval);
				callbackEnd('&TEMPO_SCADUTO=1');
		    }
		}, intervalTime);
	}
	
	function getDatiCalendario(first) {
		if(typeof(azione_eventi_cal) == "undefined") {
			azione_eventi_cal = CURRENT_ACTION;
		}
		
		if(!dragStart && !dragStartExternal) {
			$.ajax({
	            url: 'index.php?t='+azione_eventi_cal+'&f=EVENTI_CALENDARIO&DECORATION=clean',
	            data: {
	            	"societa": societa,
	            	"sito": sito,
	            	"deposito": deposito,
	            	"fornitore": fornitore,
	            	"data": data,
	            	"FROM_ACTION": CURRENT_ACTION
	            },
	            type: "POST",
	            success: function(doc) {
	            	var dati = JSON.parse(doc);
	            	//console.log(dati);
	            	if(!dragStart) {
	            		if(first) {
							//console.log(dati);
	            			slotTipoOperazione = dati.slotTipoOperazione;
	            			creaCalendario(dati);
	            		}else {
	            			$('#calendar').fullCalendar('removeEvents');
	            			$('#calendar').fullCalendar( 'addEventSource', dati.eventi);
	            		}
            			$("#message-event").html(dati.message);
	            	}else {
	            		console.log("Sono in draggg");
	            	}
	            },
	            error: function() {
	            	console.error("IN ERRORE");
	            }
	        });
		}else {
			stopRefresh();
		}
	}
	
	function startRefresh(first) {
		getDatiCalendario(first);
		var id = setInterval(getDatiCalendario, 5000);
		calendInterval.push(id);
	}
	
	function stopRefresh() {
		for(var i=0;i<calendInterval.length;i++) {
			clearInterval(calendInterval[i]);
		}
		
		calendInterval = [];
	}

	jQuery(document).ready(function() {
		/* initialize the external events
		-----------------------------------------------------------------*/
		$ = jQuery;
		
		if(!only_view) {
			$('#external-events .fc-event').each(function() {
	
				// store data so the calendar knows to render an event upon drop
				//console.log($.trim($(this).html()));
				$(this).data('event', {
					title: $(this).attr("title"), // use the element's text as the event title
					//duration: "00:30",
					tempo: $(this).attr("tempo"),
					durationEditable: false,
					myEvent: $(this).attr("gruppo"),
					prenotazione: "local",
					tipo_operazione: $(this).attr("tipo"),
					//constraint: 'businessHours',
					//constraint: 'availableForMeeting',
					//resourceId: 'a',
					stick: true // maintain when user navigates (see docs on the renderEvent method)
				});
	
				// make the event draggable using jQuery UI
				$(this).draggable({
					zIndex: 999,
					revert: true,      // will cause the event to go back to its
					revertDuration: 0,  //  original position after the drag
					start: function( event, ui ) {
						//console.log(event);
						dragObj = event.target;
						dragStartExternal = 1;
						stopRefresh();
						//console.log(dragObj);
					},
					stop: function(event, ui) {
						noClickPrenot = true;
						$(dragObj).css({"background": "", "opacity": ""});
						dragStartExternal = 0;
						setTimeout(function() {
							if(!calendInterval.length) {
								startRefresh();
							}
						}, 2000);
					},
					cursorAt: { top: 5 }
				});
			});
		}
		
		startRefresh(1);
		
		//console.log(eventi);
		creaCalendario = function (dati) {
			$('#calendar').fullCalendar('destroy');
			$('#calendar').fullCalendar({
				header: {
					left: '',
					center: 'title',
					right: only_view ? 'prev, next' : ''
				},
				titleFormat: 'dddd DD MMMM YYYY',
				allDaySlot: false,
				lang: _USER_LANGUAGE.slice(0,2).toLowerCase(),
				dragRevertDuration: 0,
				/*businessHours: {
					start: '08:00', // a start time (10am in this example)
					end: '09:00', // an end time (6pm in this example)
					//dow: [ 1, 2, 3, 4 ]
					// days of week. an array of zero-based day of week integers (0=Sunday)
					// (Monday-Thursday in this example)
				},*/
				height: $(window).innerHeight()-less_height,
				minTime: dati.minTime+":00",
				maxTime: dati.maxTime+":00",
				scrollTime: dati.minTime+":00",
				defaultView: 'agendaDay',
				defaultDate: data,
				editable: true,
				slotDuration: "0:15",
				droppable: true, // this allows things to be dropped onto the calendar
				resources: dati.slot,
				/*resources: [
					{ id: 'a', title: 'Room A' },
					{ id: 'b', title: 'Room B', eventColor: 'green' },
					{ id: 'c', title: 'Room C', eventColor: 'orange' },
					{ id: 'd', title: 'Room D', eventColor: 'red' }
				],*/
				events: dati.eventi,
				//events: eventi,
				//Si scatena quando trascino un ordine esterno dentro il calendario
				drop: function(date, jsEvent, ui, resourceId) {
					//console.log("dropppp");
					if ($('#drop-remove').is(':checked')) {
						//$(this).remove();
						$(this).css("display", "none");
					}
					
					if(alertShortbooking) {
						if(!confirm(_t['ALERT_SHORT_BOOKING'])) {
							$(this).css("display", "");
							removeDropEvent = true;
							return;
						}
					}
					
					var that = this;
					jQuery("#contError").html("");
					var durata = $(dragObj).attr("data-duration");
					durata = durata.split(":");
					var minuti = durata[0] * 60 + (+durata[1]);
					var start = date.format('YYYY-MM-DD HH:mm:ss')+".0000000";
					var end = date.add(minuti, "minutes").format('YYYY-MM-DD HH:mm:ss')+".0000000";
					
					//var ajaxInsertLocalEvent = function() {
						$.ajax({
				            url: 'index.php?t='+CURRENT_ACTION+'&f=INSERT_LOCAL_EVENT&DECORATION=clean',
				            data: {
				            	"EVENTO": {
					            	"ID_PRENOTAZIONE": 'local',
					            	"GRUPPO": $(dragObj).attr("gruppo"),
					            	"ORA_START": start,
					            	"ORA_FINE": end,
					            	"SLOT": resourceId,
					            	"TEMPO": $(dragObj).attr("tempo"),
					            	"TIPO_GRUPPO": $(dragObj).attr("tipo"),
				            	}
				            },
				            type: "POST",
				            success: function(result) {
				            	var rs = JSON.parse(result);
				            	//console.log(rs);
				            	if(!rs.succ) {
				            		jQuery("#contError").html(rs.error);
				            		$(that).css("display", "");
				            	}
				            	startRefresh();
				            },
				            error: function() {
				            	console.error("ERRORE INSERT LOCAL EVENT");
				            }
				        });
					//}
				},
				eventReceive: function(event) {
					if(removeDropEvent) {
						$('#calendar').fullCalendar('removeEvents', event._id);
						removeDropEvent = false;
					}
				},
				eventClick: function(calEvent, jsEvent, view) {
					//console.log(calEvent);
					if(calEvent.prenotazione != '0') {
						clickPrenotazioni(calEvent, jsEvent);
					}
					/*console.log(jsEvent);
					console.log(view);*/
				},
				//Si scatena quando trascino un ordine interno/esterno al calendario e senza mollare lo trascino sopra un'altro ordine o in qualsiasi punto del calendario
				eventOverlap: function(stillEvent, movingEvent) {
					//console.log("eventOverlap");
					var result = false
					var error = "";
					var overlap = null;
					
					//console.log(stillEvent.isEvento);
					//console.log(movingEvent.start.format('YYYY-MM-DD HH:mm:ss'))
					
					if(dragStartExternal && enableShortOverbooking) alertShortbooking = false;
	
					if(!enableOverbooking) {
						var tipo_operazione = slotTipoOperazione[stillEvent.resourceId];
						if(tipo_operazione != 'T' && movingEvent.tipo_operazione != 'T' && (movingEvent.tipo_operazione != tipo_operazione)) {
							error = sprintf(_t['ERRORE_SLOT_ADIBITO'], stillEvent.resourceId, (tipo_operazione == 'C' ? 'Carico' : 'Scarico'));
							overlap = false;
						}
						if(!error && !stillEvent.isEvento) {
							error = _t['NO_PRENOT_ORA'];
							if(enableShortOverbooking) {
								eventBooking = stillEvent;
								//console.log(eventBooking);
								//console.log(stillEvent.prenotazione);
								//console.log(checkPrenotazioneShortBooking(movingEvent.start, movingEvent.end, movingEvent.resourceId, movingEvent._id));
								//var copyEvent = JSON.parse(JSON.stringify(movingEvent));
								//jQuery("#contError").html(error);
								//if(stillEvent.color == 'black') return false;
								 //return true;
								if(dragStartExternal) { //Solo per gli eventi esterni per abilitare o meno il drag&drop
									if(checkPrenotazioneShortBooking(movingEvent.start, movingEvent.end, movingEvent.resourceId, movingEvent._id)) {
										alertShortbooking = true;
										error = "";
									}else {
										alertShortbooking = false;
									}
								}
							}
						}
						if(!error && (movingEvent.start.format("DD") != day || (movingEvent.end.format("DD") != day && movingEvent.end.format("HH:mm") != "00:00"))) {
							error = _t["FUORI_ORARIO"];
						}
						if(stillEvent.id == movingEvent.constraint && stillEvent.resourceId == movingEvent.resourceId) {
							//console.log("ok");
						}else if(stillEvent.allDay && movingEvent.allDay) {
							result = true;
						}
						if(!error  && !dragStart && !checkPrenotazione(movingEvent.start, movingEvent.end, movingEvent.resourceId)) {
							error = _t['TEMPI_MORTI'];
						}
					}
					
					if(error) {
						jQuery("#contError").html(error);
						if(dragStart) {
							$(".fc-dragging").css({"background": "red", border: "1px solid red;", opacity: "0.75"});
						}else {
							$(dragObj).css({"background": "red", border: "1px solid red;", opacity: "0.75"});
						}
						if(overlap == null) {
							if(enableShortOverbooking) {
								if(dragStartExternal) {
									return false;
								}else {
									if(stillEvent.color != 'black') return true;
									else return false;
								}
							}else {
								return false;
							}
						}else {
							return overlap;
						}
					}else {
						jQuery("#contError").html("");
						
						if(!dragStart) {
							$(dragObj).css({"background": "green", border: "1px solid green;", "opacity": "0.75"});
						}
						return true;
					}
				},
				/*eventMouseout: function( event, jsEvent, view ) {
					console.log("FUORII");
				},*/
				//Si scatena quando trascino un ordine interno al calendario e rilascio l'ordine
				eventDrop: function(event, delta, revertFunc) {
					if(jQuery("#contError").html()) {
						revertFunc();
					}else {
						jQuery("#contError").html("");
						
						var ajaxChangeLocalEvent = function(event) {
							$.ajax({
					            //url: 'index.php?t='+CURRENT_ACTION+'&f=CHANGE_LOCAL_EVENT&DECORATION=clean',
								url: 'index.php?t=OP_PRENOTAZIONE_SCARICHI&f=CHANGE_LOCAL_EVENT&DECORATION=clean',
					            data: {
					            	"EVENTO": {
						            	"ID_PRENOTAZIONE": event.prenotazione,
						            	"GRUPPO": event.myEvent,
						            	"ORA_START": event.start.format('YYYY-MM-DD HH:mm:ss')+".0000000",
						            	"ORA_FINE": event.end.format('YYYY-MM-DD HH:mm:ss')+".0000000",
						            	"SLOT": event.resourceId,
						            	"TEMPO": event.tempo,
						            	"TIPO_GRUPPO": event.tipo_operazione
					            	}
					            },
					            type: "POST",
					            success: function(result) {
					            	var rs = JSON.parse(result);
					            	//console.log(rs);
					            	if(!rs.succ) {
					            		revertFunc();
					            		jQuery("#contError").html(rs.error);
					            	}
					            	startRefresh();
					            },
					            error: function() {
					            	revertFunc();
					            	console.error("ERRORE INSERT LOCAL EVENT");
					            }
					        });
						}
						
						if(alertShortbooking) {
							if(confirm(_t['ALERT_SHORT_BOOKING'])) {
								ajaxChangeLocalEvent(event);
							}else {
								revertFunc();
							}
						}else {
							ajaxChangeLocalEvent(event);
						}
						alertShortbooking = false;
					}
				},
				eventDragStart: function(event, jsEvent, ui, view) {
					dragStart = 1;
					stopRefresh();
					var classe = "drag-hover";
					if(event.prenotazione != "local") classe = "drag-hover-delete";
					$('#external-events').addClass(classe);
				},
				eventDragStop: function( event, jsEvent, ui, view ) {
					dragStart = 0;
					
					var classe = "drag-hover";
					if(event.prenotazione != "local") {
						classe = "drag-hover-delete";
					}
					$('#external-events').removeClass(classe);
					
					var trashEl = jQuery('#external-events');
					var ofs = trashEl.offset();
					
					if(typeof(ofs) != 'undefined') {
						var x1 = ofs.left;
						var x2 = ofs.left + trashEl.outerWidth(true);
						var y1 = ofs.top;
						var y2 = ofs.top + trashEl.outerHeight(true);
		
						if (jsEvent.pageX >= x1 && jsEvent.pageX<= x2 &&
							jsEvent.pageY>= y1 && jsEvent.pageY <= y2) {
							// alert('SIII');
							//console.log(event);
							var remove = function () {
								$.ajax({
						            url: 'index.php?t='+CURRENT_ACTION+'&f=DELETE_PRENOTAZIONE&DECORATION=clean',
						            data: {
						            	"EVENTO": {
							            	"ID_PRENOTAZIONE": event.prenotazione,
							            	"SLOT": event.resourceId,
							            	"GRUPPO": event.myEvent
						            	}
						            },
						            type: "POST",
						            success: function(doc) {
						            	$("#external-events .fc-event[gruppo='"+event.myEvent+"']").css({"display": "", "background": "", "opacity": ""});
										$('#calendar').fullCalendar('removeEvents', event._id);
						            },
						            error: function() {
						            	console.error("ERRORE DELETE EVENTO");
						            }
						        });
							}
							
							if(event.prenotazione != "local") {
								if (confirm(sprintf(_t['CONF_ELIMINAZIONE_PRENOT'], event.prenotazione)) == true) {
									remove();
								}
							}else {
								remove();
							}
						}
						setTimeout(function() {
							if(!calendInterval.length) {
								startRefresh();
							}
						}, 2000);
					}
				},
				eventRender: function(event, element) {
					//console.log("eventRenderrr");
					if(event.myEvent !== undefined) {
						var obj = {
							'C': 'fa fa-arrow-up',
							'S': 'fa fa-arrow-down',
							'T': 'fa fa-exchange fa-rotate-90'
						}
						if(typeof(event.desc_fornitore) != "undefined") {
							element.append("<div clasS='desc_fornitore'>"+event.desc_fornitore+"</div>");
						}
						element.find(".fc-title").append("<i class='"+(obj[event.tipo_operazione])+" tipo_gruppo' style='right: 70px;'></i>");
						element.find(".fc-title").append("<label class='numTempo'>"+_t['TEMPO']+": "+event.tempo+"</label>");
					}
					
					if(dragStart) {
						dragObj = element[0];
						provaEvent = event;
						
						if(!enableOverbooking) { 
							if(!checkPrenotazione(event.start, event.end, event.resourceId, event._id)) {
								jQuery("#contError").html(_t['TEMPI_MORTI']);
							}
							if(enableShortOverbooking) {
								//console.log(event.start.format('YYYY-MM-DD HH:mm:ss'));
								//console.log(eventBooking);
								if(jQuery("#contError").html().indexOf(_t['NO_PRENOT_ORA']) != -1) {
									if(checkPrenotazioneShortBooking(event.start, event.end, event.resourceId, event._id)) {
										alertShortbooking = true;
										jQuery("#contError").html("");
									}else {
										alertShortbooking = false;
									}
									//console.log("alert eventReader");
									//console.log(alertShortbooking);
								}else {
									//console.log("alert eventReader false");
									alertShortbooking = false;
								}
							}
						}
						//$(element[0]).css("background", "red");
						var err = jQuery("#contError").html();
						if(err) {
							$(element[0]).css("background", "red");
						}else {
							$(element[0]).css("background", "green");
							jQuery("#contError").html("");
						}
						
					}
				},
				schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source'
			});
			
			if(only_view) {
				$('.fc-prev-button, .fc-next-button').click(function(){
					var d = $('#calendar').fullCalendar('getDate');
					data = d.format('YYYY-MM-DD');
					var data_view = d.format('DD/MM/YYYY');
					$('#DATA_RIF').val(data_view);
					stopRefresh();
					startRefresh(1); // 1 => per ricreare il calendario altrimenti refresha solo gli eventi
				});
			}
		}
		
	});

	
	var calEventi = {};
	
	function getEventi(risorsa, myEvent) {
		calEventi = $("#calendar").fullCalendar( 'clientEvents');
		
		//var calendarOptions = $('#calendar').fullCalendar('getView').calendar.options
		
		var arr_source = [];
		for(var i=0; i<calEventi.length; i++) {
			if(!calEventi[i].isEvento && calEventi[i]._id != myEvent) {
				if(risorsa && calEventi[i].resourceId == risorsa ) {//&& typeof(calEventi[i].chiusuraPlus) == "undefined") {
					arr_source.push(calEventi[i]);
				}
				if(!risorsa && calEventi[i].myEvent !== undefined) {
					var obj = {
						"start": calEventi[i].start.format("YYYY-MM-DD HH:mm"),
						"end": calEventi[i].end.format("YYYY-MM-DD HH:mm"),
						"resourceId": calEventi[i].resourceId,
						"tempo": calEventi[i].tempo,
						"gruppo": calEventi[i].myEvent,
						"prenotazione": calEventi[i].prenotazione,
						"tipo_operazione": calEventi[i].tipo_operazione
					}
					arr_source.push(obj);
				}
			}
		}
		//console.log(arr_source);
		return arr_source;
	}
	/*function checkTopBottomEvent2(my_hour, eventi_arr, check_top) {
		//console.log(start);
		//console.log(my_hour);
		
		if(moment(my_hour).format('HH:mm') == "00:00") return [true, 0];
		if(moment(my_hour).format('DD') != day)  return ["noday", 1];
		
		var old_diff = "";
		var top_event = "";
		for(var i=0; i<eventi_arr.length; i++) {
			var e = eventi_arr[i];
			
			if(check_top) {
				var hour_event = e.end.format('YYYY-MM-DD HH:mm');
				var diff = moment(my_hour).diff(moment(hour_event));
			}else {
				var hour_event = e.start.format('YYYY-MM-DD HH:mm');
				var diff = moment(hour_event).diff(moment(my_hour));
			}
			
			
			if(diff >= 0) {
				//console.log(diff);
				if(old_diff === "" || diff < old_diff) {
					old_diff = diff;
					top_event = e;
				}
			}
		}
		//console.log(old_diff, top_event);
		//console.log(moment.utc(old_diff).format("HH:mm"));
		//console.log(top_event.start.format("HH:mm"));
		//console.log(top_event.end.format("HH:mm"));
		//console.log(old_diff);
			
		if(top_event) { 
			if(old_diff == 0 || old_diff >= maxDuration) {
				return [true, old_diff];
			}else {
				return [false, old_diff];
			}
		}else {
			return [true, 1];
		}
	}*/
	function checkTopBottomEvent(my_hour, eventi_arr, check_top) {
		//console.log(start);
		//console.log(my_hour);
		
		//if(moment(my_hour).format('HH:mm') == "00:00") return [true, 0];
		//if(moment(my_hour).format('DD') != day)  return ["noday", 1];
		
		var old_diff = "";
		var top_event = "";
		for(var i=0; i<eventi_arr.length; i++) {
			var e = eventi_arr[i];
			
			if(check_top) {
				var hour_event = e.end.format('YYYY-MM-DD HH:mm');
				var diff = moment(my_hour).diff(moment(hour_event));
			}else {
				var hour_event = e.start.format('YYYY-MM-DD HH:mm');
				var diff = moment(hour_event).diff(moment(my_hour));
			}
			
			
			if(diff >= 0) {
				//console.log(diff);
				if(old_diff === "" || diff < old_diff) {
					old_diff = diff;
					top_event = e;
				}
			}
		}
		/*console.log(old_diff, top_event);
		console.log(moment.utc(old_diff).format("HH:mm"));
		console.log(top_event.start.format("HH:mm"));
		console.log(top_event.end.format("HH:mm"));*/
		//console.log(old_diff);
			
		if(top_event) {
			if(eventi_attaccati) { //Eventi tutti attaccati
				if((old_diff == 0 && typeof(top_event.prenotazione) != "undefined") || old_diff >= maxDuration) {
					return [true, old_diff];
				}else {
					return [false, 1];
				}
			}else {
				//Posso inserire un evento anche in prossimità di una chiusura o all'inizio e alla fine di uno slot
				if(old_diff == 0 || old_diff >= maxDuration) {
					return [true, old_diff];
				}else {
					return [false, old_diff];
				}
			}
		}else {
			return [true, 1];
		}
	}
	
	function checkPrenotazione(start, end, risorsa, myEvent) {
		//console.log(start);
		ini = start.format('YYYY-MM-DD HH:mm:ss');
		fin = end.format('YYYY-MM-DD HH:mm:ss');
		/*var start_H = start.format('HH:mm');
		var end_H = end.format('HH:mm');*/

		//console.log(start, end, risorsa);
		//console.log(moment(moment.duration(fin.diff(ini))).format("hh:mm:ss"));

		/*var now  = "04/09/2013 15:00:00";
		var then = "04/09/2013 14:20:30";*/
		//var diff = moment(fin,"DD/MM/YYYY HH:mm:ss").diff(moment(ini,"DD/MM/YYYY HH:mm:ss"));
		var eventi_arr = getEventi(risorsa, myEvent);
		if(eventi_arr.length == 2 && typeof(eventi_arr[0].chiusuraPlus) != "undefined" && typeof(eventi_arr[1].chiusuraPlus) != "undefined") {
			return true; //Non è presente alcun evento e quindi posso inserire la prenotazione in qualsiasi orario
		}else {
			event_arr = eventi_arr.reverse();
			//console.log(eventi_arr.reverse());
			var flag_libero = true;
			for (var chiave in eventi_arr) {
				//console.log(eventi_arr[chiave]);
				if(typeof(eventi_arr[chiave].prenotazione) != "undefined") {
					flag_libero = false;
					break;
				}
			}
			if(flag_libero) return true;
		}
		if(eventi_arr.length) {
			//console.log(eventi_arr);

			/*var topEvent = getTopEvent(ini, eventi_arr);
			var bottomEvent = getBottomEvent(fin, eventi_arr);*/
			var topEvent = checkTopBottomEvent(ini, eventi_arr, 1);
			var bottomEvent = checkTopBottomEvent(fin, eventi_arr, 0);
			//return true;
			if(topEvent[0] == "noday" || bottomEvent == "noday") return false;
			return (topEvent[0]  && bottomEvent[0]) || (!topEvent[1]  || !bottomEvent[1]);
		}else {
			return true;
		}

		/*console.log(ini+" -> "+fin);
		var diff = moment(fin).diff(moment(ini));
		console.log(moment.utc(diff).format("HH:mm"));*/
		//console.log(moment.duration(now.diff(then)));
	}
	function checkPrenotazioneShortBooking(start, end, risorsa, myEvent) {
		//console.log(start);
		//start = movingEvent.start.add(-timeShortOverbooking, 'minute');
		
		ini = start.format('YYYY-MM-DD HH:mm:ss');
		fin = end.format('YYYY-MM-DD HH:mm:ss'); 
		
		var eventi_arr = getEventi(risorsa, myEvent);
		if(eventi_arr.length) {
			//console.log(eventi_arr);

			var topEvent = checkTopBottomEvent(ini, eventi_arr, 1);
			var bottomEvent = checkTopBottomEvent(fin, eventi_arr, 0);
			
			/*console.log(topEvent);
			console.log(bottomEvent);*/
			
			//moment.utc(ms).format("HH:mm");
			if(!bottomEvent[1] || !topEvent[1]) {
				/*console.log(moment.utc(topEvent[1]).format("HH:mm"));
				console.log(moment.utc(bottomEvent[1]).format("HH:mm"));
				console.log(ini);
				console.log(fin);
				console.log(eventBooking);*/
				
				//if(!topEvent[1]) end = end.add(-timeShortOverbooking, 'minute');
				//if(!bottomEvent[1]) start = start.add(timeShortOverbooking, 'minute');
				
				/*console.log(start.format('YYYY-MM-DD HH:mm:ss'));
				console.log(end.format('YYYY-MM-DD HH:mm:ss'));*/
				
				//ini = start.format('YYYY-MM-DD HH:mm:ss');
				//fin = end.format('YYYY-MM-DD HH:mm:ss');
				ini_event = eventBooking.start.format('YYYY-MM-DD HH:mm:ss');
				fin_event = eventBooking.end.format('YYYY-MM-DD HH:mm:ss');
				
				if(!topEvent[1]) var diff = moment(fin).diff(moment(ini_event));
				if(!bottomEvent[1])	var diff = moment(fin_event).diff(moment(ini));
				
				var timeMiliseconds = miliseconds(0, timeShortOverbooking, 0);
				//console.log("Da sottrarre "+timeMiliseconds);
				
				//console.log(diff, diff-timeMiliseconds);
				
				//Tolgo il tempo del shortbooking
				diff = diff-timeMiliseconds;

				//console.log(moment.utc(z).format("HH:mm"), z);
				//console.log(checkPrenotazione(start, end, risorsa, myEvent));
				if(diff <= 0) return true;
				else return false;
			}else {
				return false;
			}
			
			
			//return true;
			/*if(topEvent[0] == "noday" || bottomEvent == "noday") return false;
			return (topEvent[0]  && bottomEvent[0]) || (!topEvent[1]  || !bottomEvent[1]);*/
			
		}else {
			return true;
		}
	}
	function savePrenotazioni() {
		var buttonSalva = jQuery('#salva_calendario');
		buttonSalva.attr("disabled", true);
		blockBrowser(true);
		
		$.ajax({
            url: 'index.php?t=OP_PRENOTAZIONE_SCARICHI&f=SALVA_PRENOTAZIONI&DECORATION=clean',
            data: {
            	"EVENTI": getEventi(),
            	"DATA_PRENOTAZIONE": data
            },
            type: "POST",
            success: function(res) {
            	buttonSalva.attr("disabled", true);
            	blockBrowser(false);
            	
            	var dati = JSON.parse(res);
            	if(!dati.errori.length) {
	            	//console.log(dati);
	            	callbackEnd("");
            	}else {
            		alert(dati.errori.join('\n'));
            		//console.log(dati.errori);
            	}
            },
            error: function() {
            	buttonSalva.attr("disabled", true);
            	blockBrowser(false);
            	console.error("IN ERRORE SAVE PRENOTAZIONI");
            	
            	alert(_t['ERRORE_SALVATAGGIO']);
            }
        });
	}
	function miliseconds(hrs,min,sec) {
	    return((hrs*60*60+min*60+sec)*1000);
	}
	
	function clickPrenotazioni(calEvent, jsEvent) {
		//console.log(calEvent, jsEvent);
		if(noClickPrenot) {
			noClickPrenot = false;
			return;
		}
		$.ajax({
            url: 'index.php?t=OP_PRENOTAZIONE_SCARICHI&f=EVENTI_PRECAR&DECORATION=clean',
            data: {
            	"ID_PRENOTAZIONE": calEvent.prenotazione,
            	"GRUPPO": calEvent.myEvent,
            	'ID_EVENTO': calEvent._id
            },
            type: "POST",
            success: function(doc) {
            	$("#dialog_cal").html(doc);
            	$("#dialog_cal").dialog({
            		position: { my: "left top", of: jsEvent.target},
            		closeText: "",
            		draggable: false,
            		width: 500,
            		dialogClass: 'noTitleStuff',
            		buttons: [{
    		            text: "close",
    		            click: function() {
    		              $(this).dialog("close");
    		            }
    		        }],
    		        open: function( event, ui ) {
    		        	$('.ui-dialog-buttonpane').find("span").attr("class", "button-dialog");
    		        }
				});
            },
            error: function() {
            	console.error("ERRORE OPEN DIALOG");
            }
		});
	}
	
	//Mostra calendario eliminazione prenotazione
	function eliminaPrenotazione(id_prenotazione, id_evento) {
		if(confirm('Sicuro di voler eliminare la prenotazione '+id_prenotazione)) {
			console.log("elimina "+id_prenotazione);
			$.ajax({
	            url: 'index.php?t=OP_PRENOTAZIONE_SCARICHI&f=DELETE_PRENOTAZIONE&DECORATION=clean',
	            data: {
	            	"EVENTO": {
		            	"ID_PRENOTAZIONE": id_prenotazione,
		            	"SLOT": '',
		            	"GRUPPO": ''
	            	}
	            },
	            type: "POST",
	            success: function(doc) {
	            	console.log('succ: '+id_evento);
	            	$("#dialog_cal").dialog('close');
					$('#calendar').fullCalendar('removeEvents', id_evento);
	            },
	            error: function() {
	            	console.error("ERRORE DELETE EVENTO");
	            }
	        });
		}
	}
	
	var sprintf = function(str) {
		var args = arguments,
		flag = true,
		i = 1;

		str = str.replace(/%s/g, function() {
			var arg = args[i++];
	
			if (typeof arg === 'undefined') {
				flag = false;
				return '';
			}
			return arg;
		});
		return flag ? str : '';
	};