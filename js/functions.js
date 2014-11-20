/**
 * 
 * 
 * 
 * 
 * 
 */

// limit the number of resource-entries
var limitNumber = 10;


function L(str)
{
	return langLabels[str] ? langLabels[str] : str.replace(/_/g, ' ')
}

function newEvent()
{
	showDefaultBackend(config.event.objectname, 0);
}
function styleButtons(id)
{ 
	$('#'+id+' button').each(function()
	{
		if($(this).attr('rel'))
		{
			$(this).button( {
				icons:{primary:'ui-icon-'+$(this).attr('rel')}, 
				text:(($(this).text()=='.')?false:true),
				title:''
			})
		}
	}
)};

// set a certain setting-variable
function setThings(p, v)
{
	if(typeof(p) == 'object') {
		p = p.join('.');
	};
	$.post('inc/php/setThings.php?project='+projectName,
	{
		path: p,
		val: v
	});
	//eval('settings.'+p+'='+v
};

// create resource-content
function createContent()
{
	showDefaultBackend(objectName, '0');
}

function getList()
{
	objectId='';
	cal.fullCalendar('destroy');
	cal_init(false)
}

function showResourceCal(el, name, myId)
{
	//var name = $(el).prev('em').text();
	var id = objectId = myId || $(el).parents('td').data('id');
	$('#subhead1').html('<b>'+name+'</b> <button onclick="objectId=\'\';cal.fullCalendar(\'destroy\');cal_init(false)" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" type="button" role="button" aria-disabled="false"><span class="ui-button-icon-primary ui-icon ui-icon-calculator"></span><span class="ui-button-text">'+L('show_all')+'</span></button>');
	cal.fullCalendar('destroy');
	cal_init(id);
}

function editResource(el)
{
	showDefaultBackend(objectName, $(el).parents('td').data('id'));
}
function newInResource(el)
{
	showDefaultBackend(config.event.objectname, '0&connect_to_object='+objectName+'&connect_to_id='+$(el).parents('td').data('id'));
}

function logout()
{
	$.get('crud.php', 
	{
		action: 'logout', 
		projectName: projectName
	})
	.always(function() {
		window.location = 'index.php?project='+projectName;
	})
}


function cal_init( rid )
{
	var offset = (settings['objects'][objectName] ? parseInt(settings['objects'][objectName]['offset']) : 0)
	
	
	//var d = new Date()
	//var off = d.getTimezoneOffset();
	var getParams = '?res='+res+'&limit='+limitNumber+'&offset='+offset+'&filterKey='+$('#filterSelectBox>select').val()+'&project='+projectName+'&object='+objectName;
	
	// single calendar - create a normal "new entry" button
	if (!res)
	{
		$('#subhead1').html('<button onclick="createContent()" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary" type="button" role="button" aria-disabled="false"><span class="ui-button-icon-primary ui-icon ui-icon-plus"></span><span class="ui-button-text">'+L('new_entry')+'</span></button>');
	}
	// 
	if (!rid && res)
	{
		//objectId = null;
		$.get(
			'crud.php'+getParams+'&action=getCalendarFilterHead', 
			function (data)
			{
				$('#subhead1').html(data);
				
				styleButtons('subhead1');
				
				$('#mainsearch').autocomplete({
					source: 'templates/default/search.php?actTemplate=default&project='+projectName+'&object='+objectName,
					minLength: 3,
					response: function(){
						$('body').removeClass('loading');
					},
					select: function(event,ui)
					{
						showResourceCal($('body'), ui.item.label, ui.item.id)
						return false;
					}
				});
				$("#filterSelectBox>select").on('change', function() {
					getList()
				})
			}
		)
	};
	
	window.cal = $("#calendarbody");
	
	$("#calendar").css({
		"height" : 	($(document).height() - 120) + "px",
		"width" : 	($(document).width()  - 50)  + "px"
	});
	
	
	
	getParams += '&objectId='+objectId+'&action=';
	//alert(getParams)
	
	var date = new Date();
	var d = date.getDate();
	var m = date.getMonth();
	var y = date.getFullYear();
	var rv = (config.resources[objectName] !== undefined && !rid);
	
	var options =
	{
		header: {
			 left: 'prev, next today'
			,center: 'title'
			,right: (rv ? 'resourceDay,resourceWeek,resourceNextWeeks,resourceMonth' : 'basicDay,basicWeek,month')
		},
		
		firstDay: 1,
		firstHour: 3,
		editable: false,
		selectable: false,
		minTime: 8,
		maxTime: 18,
		selectHelper: false,
		ignoreTimezone: true,
		
		events: 'crud.php' + getParams + 'getEvents',
		
		
		// after the calendar is finished
		eventAfterAllRender: function ( view )
		{
			//$('.fc-resourceName').first().html('all');
			window.rid = null;
			// place a overwiew-button in the head
			if(rid)
			{
				window.rid = rid;
				//$('.fc-header-space').first().html(' <span class="fc-button fc-resourceName fc-state-default fc-corner-left fc-corner-right">all</span> ');
			}
			// prepare resource-boxes
			$('.fc-resourceName').each(function()
			{
				var v = $.trim($(this).text());
				if(v.length==0) return;
				
				var str  = '<em>' + v + '</em> ';
					str += '<span>';
					str += '<span onclick="editResource(this)" title="'+L('edit_this_entry')+'" class="ui-button-icon-primary ui-icon ui-icon-pencil"></span>';
					str += '<span onclick="newInResource(this)" title="'+L('create_an_event_for_this_resource')+'" class="ui-button-icon-primary ui-icon ui-icon-plus"></span>';
					str += '<span onclick="showResourceCal(this,\''+v+'\')" title="'+L('show_as_single_calendar')+'" class="ui-button-icon-primary ui-icon ui-icon-calendar"></span>';
					str += '</span>';
				
				$(this)
				.html(str)
				.on('mouseover', function()
				{
					$(this).find('em').hide();
					$(this).find('span').show();
				})
				.on('mouseout', function()
				{
					$(this).find('em').show();
					$(this).find('span').hide();
				})
			});
		},
		
		// after a event was clicked
		eventClick: function ( event, jsEvent, view )  {
			showDefaultBackend(config.event.objectname, event.id);
		},
		
		windowResize: function( view ) {
			calendar.fullCalendar('option', 'height', $(window).height() - 40);
		}
	};
	
	if ( rv )
	{
		options.resources = 'crud.php'+getParams+'getResources';
		options.defaultView = 'resourceWeek';
	}
	
	calendar = cal.fullCalendar(options);
}

function showDefaultBackend ( objName, objId )
{
	getFrame('backend.php?ttemplate=default&columns=-1,0,200&project='+projectName+'&object='+objName+'#id='+objId);
}

function getFrame(url)
{
	var ww = $(window).width(),
		wh = $(window).height();
	$('#dialogbody')
		.css({'width':(ww-60)+'px',height:(wh-100)+'px'})
		.attr( 'src', url);
	
	$('#dialog').dialog({
		width: ww-20,
		height: wh-30,
		modal: true,
		show: "scale",
		close: function(event, ui)
		{ 
			$("#dialogbody").attr('src','about:blank');
		}
	});
}

function openGlobalWizard (el)
{
	getFrame(el.value);
};

$(document).ready(function() {
	
	$('#calendarhead select').addClass('ui-widget ui-state-default ui-corner-all').css('padding','5px');
	
	// calculate the amount of resources to fit into the window
	limitNumber = Math.floor(($(window).height()-130)/60);
	
	$('#objectSelect').on('change', function() {
		window.location.href = 'backend.php?project='+projectName+'&object=' + $(this).val()
	});
	
	$('#templateSelect').on('change', function() {
		window.location.href = 'backend.php?project='+projectName+'&object=' + objectName + '&template=' + $(this).val()
	});
	
	cal_init(false);
});
