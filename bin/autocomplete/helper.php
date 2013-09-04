<?php
/**
 * @author    Philip Bergman <philip@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */
include_once('Spyc.php');

$optShort = array('g:');
$optLong  = array('get:');
$options  = getopt(implode('', $optShort), $optLong);
$data     = array();
$process  = proc_open(
    'z z:dump',
    array(
        0 => array("pipe", "r"),  // stdin
        1 => array("pipe", "w"),  // stdout -> we use this
        2 => array("pipe", "w")   // stderr
    ),
    $pipes

);
/** process (YAML) output  */
if (is_resource($process)) {

    $data = Spyc::YAMLLoad(
        stream_get_contents(
            $pipes[1]
        )
    );

#    fclose($pipes[1]);

}
/** parse options from short to long key name */
foreach($optShort as $id => $opt){

    $cleanShortName = preg_replace('/:/','',$opt);
    $cleanLongName  = preg_replace('/:/','',$optLong[$id]);

    if(in_array($cleanShortName,array_keys($options))){
        $options[$cleanLongName] = $options[$cleanShortName];
        unset($options[$cleanShortName]);
    }
}

if(!isset($options['get'])){
    $options['get'] = 'all';
}

switch($options['get']){
    case 'env':
        if (isset($data['env'])) {
	    echo implode(" ", array_keys($data['env']));
        }
        break;
    default:
	$names = array();
        if ($data['tasks']) {
            foreach(array_keys($data['tasks']) as $task){
                if($task[0] !== '_'){
                    $names[] = preg_replace('/\./',':',$task);
                }
            }
            echo implode(" ", $names);
        }
}

