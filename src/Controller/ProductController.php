<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }

    #[Route('/product/add', name: 'app_product_add')]
    public function add(
        Request $request, 
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/uploads')] string $uploadsDirectory
        ): Response
    {
        $newProduct = new Product;

        $form = $this->createForm(ProductType::class, $newProduct);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            /** @var UploadedFile $thumbnail */
            $thumbnail = $form->get('thumbnail')->getData();

            if ($thumbnail) {
                //on récupère le nom original de l'image
                $originalFilename = pathinfo($thumbnail->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$thumbnail->guessExtension();
            
                // Move the file to the directory where brochures are stored
                try {
                    $thumbnail->move($uploadsDirectory, $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $newProduct->setThumbnail($newFilename);
            }

            $newProduct = $form->getData();
            $em->persist($newProduct);
            $em->flush();
        }

        return $this->render('product/add.html.twig', [
            'formulaire' => $form
        ]);
    }
}
