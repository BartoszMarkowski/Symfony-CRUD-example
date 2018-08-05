<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Omines\DataTablesBundle\Adapter\ArrayAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\Column\TwigColumn;
use Omines\DataTablesBundle\Controller\DataTablesTrait;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Filter\TextFilter;
/**
 * @Route("/user")
 */
class UserController extends Controller
{
    use DataTablesTrait;
    /**
     * @Route("/", name="user_index")
     */
    public function index(Request $request): Response
    {
        $table = $this->createDataTable([
            'jQueryUI' => false,
            'pagingType' => 'first_last_numbers',
            'lengthMenu' => [[5], [5]],
            'pageLength' => 5,
            'displayStart' => 0,
            'serverSide' => true,
            'processing' => true,
            'paging' => true,
            'lengthChange' => false,
            'ordering' => true,
            'searching' => true,
            'search' => ['smart' => false],
            'autoWidth' => false,
            'order' => [],
            'searchDelay' => 700,
            'dom' => 'lftrip',
            'orderCellsTop' => true,
            'stateSave' => false,
            'fixedHeader' => false
        ])
            ->add('id', TextColumn::class, ['searchable' => true, 'filter' => new TextFilter()])
            ->add('name', TextColumn::class, ['searchable' => true, 'filter' => new TextFilter()])
            ->add('email', TextColumn::class, ['searchable' => true, 'filter' => new TextFilter()])
            ->add('password', TextColumn::class, ['searchable' => true, 'filter' => new TextFilter()])
            ->add('buttons', TwigColumn::class, [
                'className' => 'buttons',
                'template' => 'tables/buttonbar.html.twig',
            ])
            ->createAdapter(ORMAdapter::class, [
                'entity' => User::class,
            ])->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('user/index.html.twig', ['datatable' => $table]);
    }

    /**
     * @Route("/new", name="user_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods="GET")
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', ['user' => $user]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods="GET|POST")
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_edit', ['id' => $user->getId()]);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods="DELETE")
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}
