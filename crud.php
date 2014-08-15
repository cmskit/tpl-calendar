<?php

require dirname(__DIR__) . '/default/crud.php';


class cronloader
{
	public static function autoload($className)
	{
		include_once(__DIR__ . '/Cron/' . substr($className, 5) . '.php');
	}
}

class calendar_crud extends default_crud
{
	// if we have a filter-key we overwrite all rules
	private function testFilter()
	{
		
		if ( !empty($_GET['filterKey']) && isset($_SESSION[$this->projectName]['filter'][$this->objectName][$_GET['filterKey']]) )
		{
			$f = $_SESSION[$this->projectName]['filter'][$this->objectName][$_GET['filterKey']];
			
			if (!empty($f['select'])) $this->getListFilter = $this->prepareFilterArray($f['select']);
			if (!empty($f['sort'])) $this->sortBy = $f['sort'];
			//if (!empty($f['show'])) $this->objectFields = $f['show'];
		}
	}
	
	public function getResources()
	{
		header('Content-type: application/json');
		if (empty($this->objects[$this->objectName]['config']['calendar'])) exit('no config found for this object');
		
		$conf = $this->objects[$this->objectName]['config']['calendar'];
		
		//echo '[{"name":"Resource 2","id":"resource2"},{"name":"Resource 1","id":"resource1"}]';
		//exit;
		
		include_once $this->ppath.'/objects/class.'.$this->objectName.'.php';
		$o = $this->projectName.'\\'.$this->objectName;
		$obj = new $o();
		
		$this->testFilter();
		
		$ressourcelist = $obj->GetList($this->getListFilter, $this->sortBy, $this->limit, $this->offset);
		
		
		$out = array();
		foreach($ressourcelist as $r)
		{
			$a = array('id'=>$r->id, 'name'=>'');
			if(is_array($conf['resources'][$this->objectName]['titlefields']))
			{
				foreach($conf['resources'][$this->objectName]['titlefields'] as $c)
				{
					$a['name'] .= $r->{$c} . ' ';
				}
			}
			$out[] = $a;
		}
		echo json_encode($out);
	}
	
	// filter-head for resources
	public function getCalendarFilterHead()
	{
		include_once $this->ppath.'/objects/class.'.$this->objectName.'.php';
		$o = $this->projectName.'\\'.$this->objectName;
		$obj = new $o();
		
		$this->testFilter();
		
		$ressourcelist = $obj->GetList($this->getListFilter);
		$c = count($ressourcelist);
		
		$str  = '<div><input type="text" id="mainsearch" placeholder="'.$this->L('search').'" /></div>';
		
		$str .= '<div id="filterSelectBox">'.$this->buildFilterSelect($this->objectName).'</div>';
		
		// back-Button
		$str .= $this->createButtonHtml('arrowthick-1-w',false,'settings[\'objects\'][objectName][\'offset\']-=limitNumber;setThings(\'objects\'+objectName+\'offset\', settings[\'objects\'][objectName][\'offset\']);getList()',($this->offset > 0));
		
		// pagination-Button
		//$str .= $this->strButton('arrowthick-2-e-w', false, 'showPagination()', ($this->offset > 0 || $c > $this->limit));
		
		// next-Button
		$str .= $this->createButtonHtml('arrowthick-1-e',false,'settings[\'objects\'][objectName][\'offset\']+=limitNumber;setThings(\'objects\'+objectName+\'offset\', settings[\'objects\'][objectName][\'offset\']);getList()',($c > $this->limit+$this->offset));
		
		$str .= '&nbsp;|&nbsp;';
		
		// new-Button
		if(!isset($this->disallow['newbutton'])) $str .= '<button rel="plus" onclick="createContent()" title="'.$this->L('new_entry').'">.</button>';
		
		// sort-Button
		//if(!isset($this->disallow['sortbutton'])) $str .= '<button rel="shuffle" onclick="getFrame(\'templates/default/editList.php?project='.$this->projectName.'&object='.$this->objectName.'\')" title="'.$this->L('sort').'">.</button>';
		
		$str .= '<!--lb2--><div id="pagination"></div></div>';
		
		return $str;
	}
	
	
	public function getEvents()
	{
		header('Content-type: application/json');
		if (empty($this->objects[$this->objectName]['config']['calendar'])) exit('no config found for this object');
		
		$conf = $this->objects[$this->objectName]['config']['calendar'];
		
		include_once $this->ppath.'/objects/class.'.$conf['event']['objectname'].'.php';
		
		
		// enforce numeric GET-Parameters
		$timeframe_start = intval($_GET['start']);
		$timeframe_end = intval($_GET['end']);
		
		// exit if start/end is not correct
		if($timeframe_start==0 || $timeframe_end==0 || $timeframe_start>=$timeframe_end) exit('[]');
		
		
		
		spl_autoload_register(array('cronloader', 'autoload'));
		
		
		if ($conf['event']['objectname'] != $this->objectName && !empty($this->objectId))
		{
			include_once $this->ppath.'/objects/class.'.$this->objectName.'.php';
			$o = $this->projectName.'\\'.$this->objectName;
			$obj = new $o();
			$res = $obj->Get($this->objectId);
			$method = 'get'.$conf['event']['objectname'].'List';
			$eventlist = $res->$method(array(
											array(
												array($conf['event']['startfield'], '<=', $timeframe_end),
												array($conf['event']['stopfield'],  '>=', $timeframe_start)
												)
											)
										);
			//print_r($eventlist);		
		}
		else
		{
		
			require_once $this->ppath.'/objects/class.'.$conf['event']['objectname'].'.php';
			
			$o = $this->projectName.'\\'.$conf['event']['objectname'];
			
			$obj = new $o();
			
			$eventlist = $obj->GetList(array(
											array(
												array($conf['event']['startfield'], '<=', $timeframe_end),
												array($conf['event']['stopfield'],  '>=', $timeframe_start)
												)
											)
										);
			//print_r($eventlist);
		}
		
		
		
		$events = array();
		foreach ($eventlist as $e)
		{
			$ra = array();
			if (!empty($_GET['res']))
			{
				$action = 'Get'.$_GET['res'].'List';
				$rlist = $e->$action();
				foreach($rlist as $r) $ra[] = $r->id;
				
				// only events connected to a ressource should be added to the List
				if (count($ra)==0) continue;
			}
			
			$title = '';
			foreach($conf['event']['titlefields'] as $c) $title .= $e->{$c} . ' ';
			
			$a = array(
				'id' => $e->id,
				'title' => trim($title),
				'url' => '',
				'resource' => (count($ra)==1?$ra[0]:$ra),
				'color' => (!empty($e->{$conf['event']['colorfield']}) ? $e->{$conf['event']['colorfield']} : 'yellow'),
			);
			//$conf['event']['colorfield'] 
			
			// recurring event
			if(!empty($conf['event']['cronfield']) && $e->{$conf['event']['cronfield']} !== '* * * * *') 
			{
				// use PHP Cron Expression Parser https://github.com/mtdowling/cron-expression
				$cron = Cron\CronExpression::factory($e->{$conf['event']['cronfield']});// see autoload
				
				// define time-range
				$next_start	= intval( $e->{$conf['event']['startfield']} );
				$next_end 	= intval( $e->{$conf['event']['stopfield']} );
				
				if($timeframe_start > $next_start) $next_start = $timeframe_start;
				if($timeframe_end < $next_end) $next_end = $timeframe_end;
				
				$dates = $cron->getRangeRunDates(date('Y-m-d H:i:s', $timeframe_start), date('Y-m-d H:i:s', $timeframe_end));
				//print_r($dates);
				$span = intval( $e->{$conf['event']['lengthfield']} );
				
				foreach($dates as $date)
				{
					$ts = date_format($date, 'U');
					$a['start'] = date(DateTime::ISO8601, $ts);
					$a['end'] = date(DateTime::ISO8601, ($ts + $span) );
					//$a['color'] = 'yellow';
					$a['textColor'] = 'black';
					$a['allDay'] = (($span<(60*60*24))?false:true);
					$a['recurring'] = true;
					
					$events[] = $a;
				}
				
			}
			//single event
			else
			{
				$a['start'] = date(DateTime::ISO8601, $e->{$conf['event']['startfield']});
				$a['end'] = date(DateTime::ISO8601, $e->{$conf['event']['stopfield']});
				//$a['color'] = 'orange';
				$a['textColor'] = 'black';
				$a['allDay'] = ((($e->{$conf['event']['stopfield']}-$e->{$conf['event']['startfield']})<60*60*24)?false:true);
				$a['recurring'] = false;
				
				$events[] = $a;
			}
		}

		return json_encode($events);
	}
}
// init the extended class
$c = new calendar_crud();

?>
