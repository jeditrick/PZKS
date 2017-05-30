<?php

use Acme\Graph;
use Acme\Planning\FirstFreeAlgorithm;
use Acme\SortAlgorithm;

require_once __DIR__ . '/vendor/autoload.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$system = new Graph(0);
$system->readFromFile('system.txt');
$foo = $system->getEdges();
$task = new Graph(1);
$task->readFromFile('task.txt');
$task->setNodeWeight(0, 4);
$task->setNodeWeight(1, 2);
$task->setNodeWeight(2, 5);
$task->setNodeWeight(3, 1);
$task->setNodeWeight(4, 2);
$algorithm_id = 2;
switch($algorithm_id){
    case 2:
        $planning_algorithm = new FirstFreeAlgorithm();
        break;
    case 4:
        break;
}
$planning_algorithm->execute($task, $system);

die;


$fp = fopen('php://stdin', 'r');

print "Choose graph type 1 - task graph, 0 - system graph.\n";

$graph_type = fgets($fp, 1024);
$graph = new Graph();
$graph->setGraphType(trim($graph_type));
$last_line = false;

while (!$last_line) {
    echo "\nChoose action :\n";
    echo "1. Add node\n";
    echo "2. Add edge\n";
    echo "3. Remove node\n";
    echo "4. Remove edge\n";
    echo "5. Get graph image\n";
    echo "6. Check for cycle\n";
    echo "7. Check connectivity\n";
    echo "8. Read from file\n";
    echo "9. Add node weight\n";
    echo "10. Add edge weight\n";
    echo "11. Generate graph\n";
    echo "12. Sort nodes by out coming edges \n";
    echo "13. Sort nodes by critical path \n";
    echo "14. Sort nodes by normalized critical path \n";
    echo "15. Planning \n";
    $next_line = fgets($fp, 1024); // read the special file to get the user input from keyboard
    switch(trim($next_line)){
        case 1:
            echo "Node id: \n";
            $node_id = trim(fgets($fp, 1024));
            $graph->addNode($node_id);
            break;
        case 2:
            echo "Node ids: \n";
            $nodes = explode(' ', trim(fgets($fp, 1024)));
            $graph->addEdge($nodes[0], $nodes[1]);
            break;
        case 3:
            echo "Node id: \n";
            $node_id = trim(fgets($fp, 1024));
            $graph->removeNode($node_id);
            break;
        case 4:
            echo "Node ids: \n";
            $nodes = explode(' ', trim(fgets($fp, 1024)));
            $graph->removeEdge($nodes[0], $nodes[1]);
            break;
        case 5:
            $graph->display();
            break;
        case 6:
            echo $graph->hasCycle()?"Graph has cycle\n":"Graph has no cycle\n";
            break;
        case 7:
            echo $graph->checkConnectivity()?"Graph is connected\n":"Graph isn't connected\n";
            break;
        case 8:
            echo "File name: \n";
            $file_name = trim(fgets($fp, 1024));
            $graph->readFromFile($file_name);
            $graph->setNodeWeight(0, 6);
            $graph->setNodeWeight(1, 1);
            $graph->setNodeWeight(2, 4);
            $graph->setNodeWeight(3, 25);
            $graph->setNodeWeight(4, 5);
            $graph->setNodeWeight(5, 10);
            $graph->setNodeWeight(6, 2);

            break;
        case 9:
            echo "Enter Node id and Node weight: \n";
            $node_weight = explode(' ', trim(fgets($fp, 1024)));
            $graph->setNodeWeight($node_weight[0], $node_weight[1]);
            break;
        case 10:
            echo "Enter Edge id and edge weight: \n";
            $edge_weight = explode(' ', trim(fgets($fp, 1024)));
            $graph->addEdgeWeight($edge_weight[0], $edge_weight[1], $edge_weight[2]);
            break;
        case 11:
            echo "Enter min node weight, max node weight, node count, min edge weight, max edge weight, correlation: \n";
            $params = explode(' ', trim(fgets($fp, 1024)));
            $graph->generateGraph($params[0], $params[1], $params[2], $params[3], $params[4], $params[5]);
            break;
        case 12:
            echo (new SortAlgorithm($graph))->sortByOutgoingEdges()->getSortedNodes();
            break;
        case 13:
            echo (new SortAlgorithm($graph))->sortByCriticalNodeCount()->getSortedNodes();
            break;
        case 14:
            echo (new SortAlgorithm($graph))->sortByNormalizedCriticalPath()->getSortedNodes();
            break;
        case 15:
            echo "Enter planning algorithm: \n";
            break;
    }
}
/*
1 6 12 (2 3 4)
2 4 (6 7)
*/
