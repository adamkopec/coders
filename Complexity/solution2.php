<?php

class ModelProductGroup
{

    public function getDescendantsForMenu($rootId = null, $segmentId = null, $levelLimit = null) {
        $strategy = new VeryComplexStrategy();
        $strategy->setRootId($rootId)
            ->setLevelLimit($levelLimit)
            ->setSegmentId($segmentId);

        $treeWalker = new TreeWalker($strategy);
        return $treeWalker->walk();
    }
}

class TreeWalker {
    protected $strategy;


    public function __construct(Strategy $s) {
        $this->strategy = $s;
    }

    public function walk() {
        $this->strategy->execute($this);
    }

}

class VeryComplexStrategy implements Strategy {

    protected $rootId = null;
    protected $segmentId = null;
    protected $levelLimit = null;

    public function execute()
    {
        // TODO: Implement execute() method.
    }

    public function setLevelLimit($levelLimit)
    {
        $this->levelLimit = $levelLimit;
        return $this;
    }

    public function getLevelLimit()
    {
        return $this->levelLimit;
    }

    public function setRootId($rootId)
    {
        $this->rootId = $rootId;
        return $this;
    }

    public function getRootId()
    {
        return $this->rootId;
    }

    public function setSegmentId($segmentId)
    {
        $this->segmentId = $segmentId;
        return $this;
    }

    public function getSegmentId()
    {
        return $this->segmentId;
    }
}

public interface Strategy {
    public function execute();
}