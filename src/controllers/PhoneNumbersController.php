<?php declare(strict_types=1);

namespace Bogatyrev\controllers;

use Bogatyrev\Pagination;
use Bogatyrev\PhoneNumber;
use Bogatyrev\PhoneNumberFactory;
use Psr\Http\Message\ServerRequestInterface;
use Bogatyrev\repositories\PhoneNumberRepository;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Http\Exception\BadRequestException;

class PhoneNumbersController
{
    /**
     * PhoneNumberRepository
     *
     * @var PhoneNumberRepository
     */
    private $phoneNumberRepository;

    /**
     * PhoneNumberFactory
     *
     * @var PhoneNumberFactory
     */
    private $phoneNumberFactory;

    /**
     * @param PhoneNumberRepository $phoneNumberRepository
     * @param PhoneNumberFactory $phoneNumberFactory
     */
    public function __construct(
        PhoneNumberRepository $phoneNumberRepository, 
        PhoneNumberFactory $phoneNumberFactory
    ) {
        $this->phoneNumberRepository = $phoneNumberRepository;
        $this->phoneNumberFactory = $phoneNumberFactory;
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return array
     */
    public function listItems(ServerRequestInterface $request) : array
    {
        $params = $request->getQueryParams();
        $search = $params['search'] ?? null;
        $total = $this->phoneNumberRepository->listCount(null, null, $search);
        $pagination = new Pagination($request, $total);
        
        $results = $this->phoneNumberRepository->list($pagination->getLimit(), $pagination->getOffset(), $search);
        
        return $this->createListResponse($results, $pagination);
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return array
     */
    public function retrieveItem(ServerRequestInterface $request, array $args) : array
    {
        $id = $this->getIdParam($args);
        $phoneNumber = $this->phoneNumberRepository->retrieve($id);

        if ($phoneNumber === null) {
            throw new NotFoundException("Phone number '$id' not found");
        }

        return $phoneNumber->toArray();
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return array
     */
    public function createItem(ServerRequestInterface $request)
    {
        $json = $request->getBody()->getContents();
        $phoneNumber = $this->phoneNumberFactory->createFromJson($json);
        if ($phoneNumber === null || ! $phoneNumber->validate()) {
            throw new BadRequestException("Invalid data");
        }
        $phoneNumber->updated();
        $result = $this->phoneNumberRepository->save($phoneNumber);

        return [
            'success' => $result,
            'id' => $phoneNumber->getId(),
        ];
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return array
     */
    public function updateItem(ServerRequestInterface $request, array $args)
    {
        $id = $this->getIdParam($args);
        $phoneNumberOld = $this->phoneNumberRepository->retrieve($id);

        if ($phoneNumberOld === null) {
            throw new NotFoundException("Phone number '$id' not found");
        }
        
        $json = $request->getBody()->getContents();
        $phoneNumber = $this->phoneNumberFactory->createFromJson($json);
        if ($phoneNumber === null || ! $phoneNumber->validate()) {
            throw new BadRequestException("Invalid data");
        }
        $phoneNumber->setId($phoneNumberOld->getId());
        $phoneNumber->updated();
        $result = $this->phoneNumberRepository->save($phoneNumber);
        
        return [
            'success' => $result,
            'id' => $phoneNumber->getId(),
        ];
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return array
     */
    public function deleteItem(ServerRequestInterface $request, array $args)
    {
        $id = $this->getIdParam($args);
        $phoneNumber = $this->phoneNumberRepository->retrieve($id);

        if ($phoneNumber === null) {
            throw new NotFoundException("Phone number '$id' not found");
        }
        $result = $this->phoneNumberRepository->delete($phoneNumber->getId());

        return [
            'success' => $result,
            'id' => $phoneNumber->getId(),
        ];
    }

    /**
     * @param array $args
     * @return integer
     */
    private function getIdParam(array $args): int
    {
        if (! isset($args['id']) || empty($args['id'])) {
            throw new BadRequestException("Param 'id' is required");
        }
        
        return (int) $args['id'];
    }

    /**
     * @todo Move to factory
     *
     * @param array $results
     * @param Pagination $pagination
     * @return array
     */
    private function createListResponse(array $results, Pagination $pagination): array
    {
        $response = [];
        /** @var PhoneNumber $phoneNumber */
        foreach ($results as $phoneNumber) {
            $response['results'][] = $phoneNumber->toArray();
        }
        $response['limit'] = $pagination->getLimit();
        $response['total'] = $pagination->getTotal();
        $response['_links'] = [
            'self' => $pagination->getCurrentUri(),
        ];
        if ($pagination->hasPrev()) {
            $response['_links']['prev'] = $pagination->getPrevUri();
        }
        if ($pagination->hasNext()) {
            $response['_links']['next'] = $pagination->getNextUri();
        }

        return $response;
    }
}
