<?php
/*
 * Przez zamówienie rozumiemy obiekt, który zawiera:
 * - datę złożenia,
 * - wartość netto,
 * - wartość brutto,
 * - inne dane
 */
class Order
{
    public $date, $netValue, $grossValue;
}

/**
 * źródło danych zamówień
 */
interface DataSource
{
    /**
     * Pobierz zamówienia jako tablicę
     * @return Order[]|array
     */
    public function getOrdersAsArray();
}

interface Filter
{
    public function apply(Query $query);
}

class Filter_DateLessThan implements Filter
{

    protected $format = 'Y-m-d'; //setFormat/getFormat omitted
    /** @var DateTime */
    protected $date;

    public function __construct(DateTime $date)
    {
        $this->date = $date;
    }

    public function apply(Query $query)
    {
        $query->where('Order.date < ?', $this->date->format($this->format));
    }
}

class OrderDataSource implements DataSource
{

    /** @var Filter[]|array */
    protected $filters = [];

    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * Pobierz zamówienia jako tablicę
     * - filtrując je według zdefiniowanych filtrów
     *
     * @return array|Order[]
     */
    public function getOrdersAsArray()
    {
        $query = $this->_getFilteredQuery();
        return $query->execute();
    }

    protected function _getFilteredQuery()
    {
        $query = $this->_prepareQuery();
        foreach ($this->filters as $filter) {
            $filter->apply($query);
        }
        return $query;
    }
}

class OrderServiceException extends Exception{}

class OrderService
{
    const SCALE = 2;

    protected $isFromNet = true; //setFromNet(), setFromGross()

    /** @var DataSource */
    private $dataSource;
    private $sum = 0;
    private $counter = 0;

    public function __construct(DataSource $source)
    {
        $this->dataSource = $source;
    }

    /**
     * @throws OrderServiceException
     */
    public function getCountedAverage()
    {
        $orderIterator = $this->_getValidIterator();

        foreach ($orderIterator as $order) {
            $value = $this->getValue($order);
            $this->addToSum($value);
            $this->incrementCounter();
        }

        return $this->divideSumByCounter();
    }

    protected function _getValidIterator()
    {
        $orderIterator = new ArrayIterator($this->dataSource->getOrdersAsArray());
        if ($this->isOrderCountPositive($orderIterator)) {
            throw new OrderServiceException("Brak zamówień, z których wartości można by policzyć średnią");
        }
        return $orderIterator;
    }

    protected function isOrderCountPositive(ArrayIterator $orderIterator)
    {
        return $orderIterator->count() == 0;
    }

    protected function getValue(Order $order)
    {
        if ($this->isFromNet) {
            return $order->netValue;
        } else {
            return $order->grossValue;
        }
    }

    private function addToSum($value)
    {
        $this->sum = bcadd($this->sum, $value, self::SCALE);
    }

    private function incrementCounter()
    {
        $this->counter++;
    }

    private function divideSumByCounter()
    {
        return bcdiv($this->sum, $this->counter, self::SCALE);
    }
}

//wywołanie

$filters = [
    new Filter_DateLessThan(new DateTime("2012-03-12")),
    new Filter_DateGreaterThan(new DateTime("2012-01-10"))
];

$dataSource = new OrderDataSource();
$dataSource->setFilters($filters);

$service = new OrderService($dataSource);
$service->setFromGross();

echo $service->getCountedAverage();