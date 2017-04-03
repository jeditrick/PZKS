<?php
namespace Acme;

class Graph
{

    public function __construct($file = null)
    {
        if($file){
            $raw_data = explode("\n", file_get_contents(dirname(__DIR__).'/'.$file));
            foreach($raw_data as $data){
                $edge = explode(' ', $data);
                if(isset($edge[1])){
                    if(!isset($this->adjacency_list[$edge[1]])){
                        $this->adjacency_list[$edge[1]] = [];
                        $this->vertex_data[$edge[1]] = null;
                    }
                    $this->adjacency_list[$edge[0]][$edge[1]] = isset($edge[2])?$edge[2]:null;
                }else{
                    $this->adjacency_list[$edge[0]] = [];
                }
                $this->vertex_data[$edge[0]] = null;
            }
        }
    }


    /**
     * Adds an undirected edge between $u and $v in the graph.
     *
     * $u,$v can be anything.
     *
     * Edge (u,v) and (v,u) are the same.
     *
     * $data is the data to be associated with this edge.
     * If the edge (u,v) already exists, nothing will happen (the
     * new data will not be assigned).
     */
    public function addEdge($u, $v, $data = null)
    {
        assert($this->sanityCheck());
        assert($u != $v);

        if ($this->hasEdge($u, $v)) {
            return;
        }

        //If u or v don't exist, create them.
        if (!$this->hasVertex($u)) {
            $this->addVertex($u);
        }
        if (!$this->hasVertex($v)) {
            $this->addVertex($v);
        }

        //Some sanity.
        assert(array_key_exists($u, $this->adjacency_list));
        assert(array_key_exists($v, $this->adjacency_list));

        //Associate (u,v) with data.
        $this->adjacency_list[$u][$v] = $data;
        //Associate (v,u) with data.
        $this->adjacency_list[$v][$u] = $data;

        //We just added two edges
        $this->edge_count += 2;

        assert($this->hasEdge($u, $v));

        assert($this->sanityCheck());
    }

    public function hasEdge($u, $v)
    {
        assert($this->sanityCheck());

        //If u or v do not exist, they surely do not make up an edge.
        if (!$this->hasVertex($u)) {
            return false;
        }
        if (!$this->hasVertex($v)) {
            return false;
        }


        //some extra sanity.
        assert(array_key_exists($u, $this->adjacency_list));
        assert(array_key_exists($v, $this->adjacency_list));

        //This is the return value; if v is a neighbor of u, then its true.
        $result = array_key_exists($v, $this->adjacency_list[$u]);

        //Make sure that iff v is a neighbor of u, then u is a neighbor of v
        assert($result == array_key_exists($u, $this->adjacency_list[$v]));

        return $result;
    }

    /**
     * Remove (u,v) and return data.
     */
    public function removeEdge($u, $v)
    {
        assert($this->sanityCheck());

        if (!$this->hasEdge($u, $v)) {
            return null;
        }

        assert(array_key_exists($u, $this->adjacency_list));
        assert(array_key_exists($v, $this->adjacency_list));
        assert(array_key_exists($v, $this->adjacency_list[$u]));
        assert(array_key_exists($u, $this->adjacency_list[$v]));

        //remember data.
        $data = $this->adjacency_list[$u][$v];

        unset($this->adjacency_list[$u][$v]);
        unset($this->adjacency_list[$v][$u]);

        //We just removed two edges.
        $this->edge_count -= 2;

        assert($this->sanityCheck());

        return $data;
    }

    //Return data associated with (u,v)
    public function getEdgeData($u, $v)
    {
        assert($this->sanityCheck());

        //If no such edge, no data.
        if (!$this->hasEdge($u, $v)) {
            return null;
        }

        //some sanity.
        assert(array_key_exists($u, $this->adjacency_list));
        assert(array_key_exists($v, $this->adjacency_list[$u]));


        return $this->adjacency_list[$u][$v];
    }

    /**
     * Add a vertex. Vertex must not exist, assertion failure otherwise.
     */
    public function addVertex($u, $data = null)
    {
        assert(!$this->hasVertex($u));

        //Associate data.
        $this->vertex_data[$u] = $data;
        //Create empty neighbor array.
        $this->adjacency_list[$u] = [];

        assert($this->hasVertex($u));
        assert($this->sanityCheck());
    }

    public function hasVertex($u)
    {
        assert($this->sanityCheck());
        assert(array_key_exists($u, $this->vertex_data) == array_key_exists($u, $this->adjacency_list));

        return array_key_exists($u, $this->vertex_data);
    }

    //Returns data associated with vertex, null if vertex does not exist.
    public function getVertexData($u)
    {
        assert($this->sanityCheck());

        if (!array_key_exists($u, $this->vertex_data)) {
            return null;
        }

        return $this->vertex_data[$u];
    }

    //Count the neighbors of a vertex.
    public function countVertexEdges($u)
    {
        assert($this->sanityCheck());

        if (!$this->hasVertex($u)) {
            return 0;
        }

        //some sanity.
        assert(array_key_exists($u, $this->adjacency_list));

        return count($this->adjacency_list[$u]);
    }

    /**
     * Return an array of neighbor vertices of u.
     * If $with_data == true, then it will return an associative array, like so:
     * {neighbor => data}.
     */
    public function getEdgeVertices($u, $with_data = false)
    {
        assert($this->sanityCheck());

        if (!array_key_exists($u, $this->adjacency_list)) {
            return [];
        }

        $result = [];

        if ($with_data) {
            foreach ($this->adjacency_list[$u] as $v => $data) {
                $result[$v] = $data;
            }
        } else {

            foreach ($this->adjacency_list[$u] as $v => $data) {
                array_push($result, $v);
            }
        }

        return $result;
    }

    //Removes a vertex if it exists, and returns its data, null otherwise.
    public function removeVertex($u)
    {
        assert($this->sanityCheck());

        //If the vertex does not exist,
        if (!$this->hasVertex($u)) {
            //Sanity.
            assert(!array_key_exists($u, $this->vertex_data));
            assert(!array_key_exists($u, $this->adjacency_list));

            return null;
        }

        //We need to remove all edges that this vertex belongs to.
        foreach ($this->getEdgeVertices($u) as $v) {
            $this->removeEdge($u, $v);
        }


        //After removing all such edges, u should have no neighbors.
        assert($this->countVertexEdges($u) == 0);

        //sanity.
        assert(array_key_exists($u, $this->vertex_data));
        assert(array_key_exists($u, $this->adjacency_list));

        //remember the data.
        $data = $this->vertex_data[$u];

        //remove the vertex from the data array.
        unset($this->vertex_data[$u]);
        //remove the vertex from the adjacency list.
        unset($this->adjacency_list[$u]);

        assert($this->sanityCheck());

        return $data;
    }

    public function getVertexCount()
    {
        assert($this->sanityCheck());

        return count($this->vertex_data);
    }

    public function getEdgeCount()
    {
        assert($this->sanityCheck());

        //edge_count counts both (u,v) and (v,u)
        return $this->edge_count / 2;
    }

    public function getVertexList($with_data = false)
    {
        $result = [];

        if ($with_data) {
            foreach ($this->vertex_data as $u => $data) {
                $result[$u] = $data;
            }
        } else {
            foreach ($this->vertex_data as $u => $data) {
                array_push($result, $u);
            }
        }

        return $result;
    }


    public function edgeListStrArray($ordered = true)
    {
        $result_strings = [];
        foreach ($this->vertex_data as $u => $udata) {
            foreach ($this->adjacency_list[$u] as $v => $uv_data) {
                if (!$ordered || ($u < $v)) {
                    array_push($result_strings, '(' . $u . ',' . $v . ')');
                }
            }
        }

        return $result_strings;
    }

    public function sanityCheck()
    {
        if (count($this->vertex_data) != count($this->adjacency_list)) {
            return false;
        }

        $edge_count = 0;

        foreach ($this->vertex_data as $v => $data) {

            if (!array_key_exists($v, $this->adjacency_list)) {
                return false;
            }

            $edge_count += count($this->adjacency_list[$v]);
        }

        if ($edge_count != $this->edge_count) {
            return false;
        }

        if (($this->edge_count % 2) != 0) {
            return false;
        }

        return true;
    }


    public function writeGraphInFile()
    {
        $f = fopen(date('Y-m-d H:i').'.txt', 'w');
        fwrite($f, var_export($this->adjacency_list, true));
    }

    /**
     * This keeps an array that associates vertices with their neighbors like so:
     *
     * {<vertex> => {<neighbor> => <edge data>}}
     *
     * Thus, each $adjacency_list[$u] = array( $v1 => $u_v1_edge_data, $v2 => $u_v2_edge_data ...)
     *
     * The edge data can be null.
     */
    protected $adjacency_list = [];

    /**
     * This associates each vertex with its data.
     *
     * {<vertex> => <data>}
     *
     * Thus each $vertex_data[$u] = $u_data
     */
    protected $vertex_data = [];

    /**
     * This keeps tracks of the edge count so we can retrieve the count in constant time,
     * instead of recounting. In truth this counts both (u,v) and (v,u), so the actual count
     * is $edge_count/2.
     */
    protected $edge_count = 0;
}