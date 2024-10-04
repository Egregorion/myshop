<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(): Response
    {
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }

    #[Route('/category/add', name: 'app_category_add')]
    // Optionally, you can set a custom message that will be displayed to the user
    #[IsGranted('ROLE_ADMIN', message: 'You are not allowed to access the admin dashboard.')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        //création d'un objet vide de type Category
        $newcategory = new Category;
        //initialise un formulaire à partir de la classe de formulaire correspondant à cette entité, puis on le relie à l'objet vide        
        $form = $this->createForm(CategoryType::class, $newcategory);

        //on demande au formulaire de traiter les requêtes, pour cela on lui fourni un objet request injecté dans la fonction add()
        $form->handleRequest($request);

        //on va maintenant lui expliquer quoi faire avec les données
        if($form->isSubmitted() && $form->isValid()){
            //on va remplir l'objet avec les données du formulaire 
            $newcategory = $form->getData();
            //on utilise le manager global pour "sauvegarder" l'entité
            $entityManager->persist($newcategory);
            //on envoie en bdd
            $entityManager->flush();
        }

        return $this->render('category/add.html.twig', [
            //on envoie le formulaire à la vue
            'formulaire'=>$form
        ]);
    }

    #[Route('/category/{id}', name: 'app_category_show')]
    public function show(CategoryRepository $cr, $id):Response
    {
        $category = $cr->find($id);
        dump($category);
        
        return $this->render('category/show.html.twig', [
            'category' => $category
        ]);
    }
}
