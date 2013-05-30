<?php
class product_Model_Lister
{
    protected $invisibleCols = array();
    protected $loadData = true;

    public function getListDg(ProductListRequest $request, $prefix = null) {

        //trochę mniej linii


        return $oDg;
    }

    public function setInvisibleCols($invisibleCols)
    {
        $this->invisibleCols = $invisibleCols;
    }

    public function getInvisibleCols()
    {
        return $this->invisibleCols;
    }

    public function setLoadData($loadData)
    {
        $this->loadData = $loadData;
    }

    public function getLoadData()
    {
        return $this->loadData;
    }
}

class ProductListRequest {
    protected $parameters;
    protected $filters;

    public static function fromHttpRequest(Zend_Http_Request $request) {
        return new self(
            ProductFilterCollection::fromPost($request->getPost()),
            PageParameterCollection::fromGet($request->getQuery())
        );
    }

    public function __construct(ProductFilterCollection $filters, PageParameterCollection $parameters) {
        $this->filters = $filters;
        $this->parameters = $parameters;
    }

    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    public function getParameters()
    {
        return $this->parameters;
    }
}