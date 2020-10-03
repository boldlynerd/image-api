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
        try {
            $this->imageRepository->create(
                $this->imageRepository->load(
                    trim($request->get('userName')),
                    trim($request->get('imageName'))
                ),
                $request->get('base64Image')
            );

            //todo would be nice to return the URL directly
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()]);
        }
    }

    /**
     * @Route("/api/images/urls/{userName}/{imageName}", methods={"GET"})
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
     * @Route("/api/images/user/{userName}", methods={"GET"})
     *
     * @param string $userName
     *
     * @return JsonResponse
     */
    public function getByUser(string $userName)
    {
        return new JsonResponse(['success' => true, 'images' => $this->imageRepository->loadImageListByUserName($userName)]);
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