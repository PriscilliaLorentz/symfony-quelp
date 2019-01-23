<?php

namespace Quelp\ImageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use DB\Bundle\dbBundle\Entity\Multimedia;
use DB\Bundle\dbBundle\Entity\Album;
use DB\Bundle\dbBundle\Form\MultimediaType;
use DB\Bundle\dbBundle\Form\AlbumType;


class AlbumsController extends Controller
{
    public function indexAction()
    {
    	$security = $this->container->get('security.context');
		if (!$security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
		    return $this->redirect($this->generateUrl('fos_user_security_login'));
		}

		$token = $security ->getToken();  // Si la requête courante n'est pas derrière un pare-feu, $token est null
		$userAccount = $token->getUser();// Sinon , on récupère l'utilisateur
		$em = $this->getDoctrine() ->getManager();

    	$albums = new Album();
    	$Image = new Multimedia();

    	$albums ->setUser($userAccount);
    	$Image ->setUser($userAccount);
    	$formImg = $this->CreateForm(new MultimediaType, $Image);
    	$formAlb = $this->CreateForm(new AlbumType, $albums);

    	$request = $this->get('request');
    	if ($request ->getMethod() == 'POST') {
    		$formImg->bind($request);
    		$formAlb->bind($request);
    		if ($formImg->IsValid()) {
    			if ($formAlb->IsValid()) {
	    			$em->persist($albums);
					$em->flush();
					$Image ->setAlbum($albums);
    			}
                if ($Image->getEditDate() != null) {
                    $em->persist($Image);
				    $em->flush();
                }
    		}
    	}

    	$list_albums = $em->getRepository('DBdbBundle:Album') ->findUser($userAccount);

        return $this->render('QuelpImageBundle:Gallerie:albums.html.twig', array('img_form' => $formImg->createView(),
        						'alb_form' => $formAlb->createView(),
        						'list_albums' => $list_albums));
    }

    public function editAction() {

        $security = $this->container->get('security.context');
        if (!$security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $request = $this->get('request');
        $token = $security ->getToken();
        $userAccount = $token->getUser();
        $em = $this->getDoctrine() ->getManager();

        if ($request->query->get('id') != null) {
            $id = $request->query->get('id');
            $album = $em->getRepository('DBdbBundle:Album') ->findById($id);

            if ($request ->getMethod() =='POST') {
                $name = $request->request->get('alb_name');
                $album[0]->setName($name);
                $album[0]->setEditDate(new \Datetime());
                $em->persist($album[0]);
                $em->flush();
               // return new Response('Album(s) deleted successfully');
            }
        }
        return $this->forward('QuelpImageBundle:Albums:index');
    }

    public function deleteAction() {

        $security = $this->container->get('security.context');
        if (!$security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $request = $this->get('request');
        $token = $security ->getToken();
        $userAccount = $token->getUser();
        $em = $this->getDoctrine() ->getManager();

        if ($request ->getMethod() =='POST') {
            for ($i = 0; isset($request->request->get('box')[$i]); $i++ ) {
                $boxes = $request->request->get('box')[$i];
                $album = $em->getRepository('DBdbBundle:Album') ->findById($boxes);
                if (isset($album[0])) {
                    $images_album = $em->getRepository('DBdbBundle:Multimedia') ->findByAlbum($album[0]);
                    for ($j = 0; isset($images_album[$j]); $j++ ) {
                        $em->remove($images_album[$j]);
                    }
                    $em->remove($album[0]);
                }
            }
            $em->flush();
           // return new Response('Album(s) deleted successfully');
        }
        return $this->forward('QuelpImageBundle:Albums:index');
    }
}