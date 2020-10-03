<?php

namespace App\Controller;

use App\Repository\AwsImageRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ImageController
{
    /**
     * @var AwsImageRepository
     */
    private AwsImageRepository $imageRepository;

    /**
     * ImageController constructor.
     * @param AwsImageRepository $awsImageRepository
     */
    public function __construct(AwsImageRepository $awsImageRepository)
    {
        $this->imageRepository = $awsImageRepository;
    }

    /**
     * @Route("/api/images/", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $this->imageRepository->create(
            $this->imageRepository->load($request->Get('userName'), $request->get('imageName')),
            $request->get('base64Image')
        );

        //todo nice to return the URL directly
        return new JsonResponse(['success' => true]);
    }

    /**
     * @Route("/api/imageUrl/{userName}/{imageName}", methods={"GET"})
     * @param string $userName
     * @param string $imageName
     *
     * @return JsonResponse
     */
    public function getUrl(string $userName, string $imageName)
    {
        $image = $this->imageRepository->load($userName, $imageName);
        return new JsonResponse(['success' => true, 'url' => $this->imageRepository->getUrl($image)]);
    }

    /**
     * @Route("/api/images/{key}", methods={"GET"})
     *
     * @param string $key
     */
    public function read(string $key)
    {
        //stub, not part of HomeCase functionality
    }

    /**
     * @Route("/api/images/user/{userName}", methods={"GET"})
     *
     * @param string $userName
     */
    public function readByUser(string $userName)
    {
        var_dump($this->imageRepository->loadImageListByUserName($userName));
        die('hi ' . $userName);
    }

    /**
     * @Route("/api/images/", methods={"PUT"})
     *
     * @param Request $request
     */
    public function update(Request $request)
    {
        //stub, not part of HomeCase functionality
    }

    /**
     * @Route("/api/images/{key}", methods={"DELETE"})
     * @param string $key
     */
    public function delete(string $key)
    {
        //stub, not part of HomeCase functionality
    }
}