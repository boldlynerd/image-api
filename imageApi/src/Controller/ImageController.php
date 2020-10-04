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
            $image = $this->imageRepository->load(
                trim($request->get('userName', '')),
                trim($request->get('imageName', ''))
            );

            $this->imageRepository->create(
                $image,
                $request->files->get('image')
            );

            return new JsonResponse([
                'success' => true,
                'url' => $this->imageRepository->getUrl($image)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'errorCode' => $e->getCode(),
                'errorMessage' => $e->getMessage()
            ]);
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
        try {
            $image = $this->imageRepository->load($userName, $imageName);
            return new JsonResponse(['success' => true, 'url' => $this->imageRepository->getUrl($image)]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()]);
        }
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
        // todo could check if user found, but returning an empty array is also correct,
        // since we are not persisting users anywhere
        try {
            return new JsonResponse([
                'success' => true,
                'images' => $this->imageRepository->loadImageListByUserName($userName)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()]);
        }
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