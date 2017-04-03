<?php


namespace Acme;


class TaskGraph extends Graph
{
    public function addEdge($u, $v, $data = null)
    {
        assert($this->sanityCheck());
        assert($u != $v);

        if ($this->hasEdge($u, $v) || $this->hasEdge($v, $u)) {
            echo "You already have edge $v - $u";
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


        //We just added two edges
        $this->edge_count += 1;

        assert($this->hasEdge($u, $v));

        assert($this->sanityCheck());
    }

    public function removeEdge($u, $v)
    {
        assert($this->sanityCheck());

        if (!$this->hasEdge($u, $v)) {
            return null;
        }

        assert(array_key_exists($u, $this->adjacency_list));
        assert(array_key_exists($v, $this->adjacency_list));
        assert(array_key_exists($v, $this->adjacency_list[$u]));

        //remember data.
        $data = $this->adjacency_list[$u][$v];

        unset($this->adjacency_list[$u][$v]);

        //We just removed two edges.
        $this->edge_count -= 1;

        assert($this->sanityCheck());

        return $data;
    }

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
        foreach ($this->adjacency_list as $k => $v) {
            $this->removeEdge($u, $v);
            $this->removeEdge($k, $u);
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

        return $result;
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

        return true;
    }



}