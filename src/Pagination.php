<?php 
namespace Bogatyrev;

use Psr\Http\Message\ServerRequestInterface;

class Pagination
{
    protected const DEFAULT_LIST_LIMIT = 20;
    protected const DEFAULT_PAGE = 1;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $total;

    /**
     * @var ServerRequestInterface
     */
    private $request;

    protected $current;

    public function __construct(ServerRequestInterface $request, $total)
    {
        $this->setRequest($request);
        $this->setTotal($total);

        $params = $this->getRequest()->getQueryParams();
        $limit = (int) ($params['limit'] ?? self::DEFAULT_LIST_LIMIT);
        $page = (int) ($params['page'] ?? self::DEFAULT_PAGE);
        $this->setPage($page);
        $this->setLimit($limit);
    }

    public function hasNext(): bool
    {
        return ($this->getLimit() + $this->getOffset()) < $this->getTotal();
    }

    public function getNextUri(): string
    {
        return $this->getBaseUri() . '?' . http_build_query(\array_merge([
            'page' => $this->getNextPage(),
            'limit' => $this->getLimit(),
        ], $this->getParams()));
    }

    public function getPrevUri(): string
    {
        return $this->getBaseUri() . '?' . http_build_query(\array_merge([
            'page' => $this->getPrevPage(),
            'limit' => $this->getLimit(),
        ], $this->getParams()));
    }

    public function getCurrentUri(): string
    {
        return $this->getBaseUri() . '?' . http_build_query(\array_merge([
            'page' => $this->getPage(),
            'limit' => $this->getLimit(),
        ], $this->getParams()));
    }

    public function getNextPage(): int
    {
        return $this->getPage() + 1;
    }

    public function getPrevPage(): int
    {
        return $this->getPage() - 1;
    }

    public function hasPrev(): bool
    {
        return ($this->getPage() - 1) > 0;
    }

    public function getOffset(): int
    {
        return ($this->getPage() - 1) * $this->getLimit();
    }

    protected function getBaseUri(): string
    {
        $uri = $this->getRequest()->getServerParams()["REQUEST_URI"];
        return strtok($uri, '?');
    }

    protected function getParams(): array
    {
        $queryStr = $this->getRequest()->getServerParams()['QUERY_STRING'];
        \parse_str($queryStr, $params);
        if (isset($params['limit'])) {
            unset($params['limit']);
        }
        if (isset($params['page'])) {
            unset($params['page']);
        }

        return $params;
    }

    /**
     * Get the value of total
     *
     * @return  int
     */ 
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * Set the value of total
     *
     * @param  int  $total
     *
     * @return  self
     */ 
    public function setTotal(int $total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get the value of limit
     *
     * @return  int
     */ 
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Set the value of limit
     *
     * @param  int  $limit
     *
     * @return  self
     */ 
    public function setLimit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Get the value of page
     *
     * @return  int
     */ 
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Set the value of page
     *
     * @param  int  $page
     *
     * @return  self
     */ 
    public function setPage(int $page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get the value of request
     *
     * @return  ServerRequestInterface
     */ 
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * Set the value of request
     *
     * @param  ServerRequestInterface  $request
     *
     * @return  self
     */ 
    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }
}