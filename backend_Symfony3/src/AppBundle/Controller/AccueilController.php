<?php

namespace AppBundle\Controller;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Filesystem\Filesystem;
use AppBundle\Entity\User as User;
class AccueilController extends Controller
{



  /**
   * @Route("/createUser", name="create")
   */
  public function createAccountAction(Request $request)
  {
      $factory = $this->get('security.encoder_factory');
      $fs = new Filesystem();
      $data = json_decode(file_get_contents('php://input'),true);
      $fs->dumpfile('/tmp/log.txt', 'yaaay');
      $user=new User();
      $newprofile=$data['user'];
      $user->setUsername($newprofile['pseudo']);
      $user->setEmail($newprofile['email']);
      $user->setAge($newprofile['age']);
      $user->setFamille($newprofile['famille']);
      $user->setRace($newprofile['race']);
      $user->setNourriture($newprofile['nourriture']);

      $encoder = $this->container->get('security.password_encoder');
      $encoded = $encoder->encodePassword($user, $newprofile['mdp']);
      $user->setPassword($encoded);

      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($user);
      $entityManager->flush();

      $serializer = $this->container->get('jms_serializer');

      $response= array('status' =>'success' ,
                        'profile'=>$serializer->toArray($user),
                        'error'=>'',
                        'token'=>''
      );
      $myresponse=new JsonResponse($response,200,array('Access-Control-allow-origin' =>'*'));
      return $myresponse;

  }




  /**
   * @Route("/accueil", name="accueil")
   */
  public function indexAction(Request $request)
  {
  #header('Access-Control-Allow-Origin: *');
    $factory = $this->get('security.encoder_factory');
    $fs = new Filesystem();
    $data = json_decode(file_get_contents('php://input'),true);
    $fs->dumpfile('/tmp/log.txt', 'yaaay');
    $pseudo = $data['pseudo'];
    $pass = $data['mdp'];

    //var_dump($data);
    $user=$this->getDoctrine()->getRepository('AppBundle\Entity\User')->findOneBy(array('username' =>$pseudo));
    //$user = $this->getUser();

    if (!$user) {

      $response = array(
        'status' => 'failed',
        'username' => $data['pseudo'],
        'error' => 'user doesnt exist',
        'token' => ''
      );
      $myresponse =new JsonResponse($response, 200, array('Access-Control-Allow-Origin'=> '*'));
      json_decode($myresponse);
      return $myresponse;
    }
    $encoder = $factory->getEncoder($user);
    $salt = $user->getSalt();
    if(!$encoder->isPasswordValid($user->getPassword(), $pass, $salt)) {
      $response = array(
              'status' => 'failed',
              'username' => $pseudo,
              'id' => 'empty',
              'error' => 'mot de passe faux',
              'token' => ' '
     );
     $myresponse =new JsonResponse($response, 200, array('Access-Control-Allow-Origin'=> '*'));
     json_decode($myresponse);
     return $myresponse;
   }
   $response = array(
           'status' => 'success',
           'username' => $user->getUsername(),
           'id' => $user->getId(),
           'error' => '',
           'token' => ' '
  );
  $myresponse =new JsonResponse($response, 200, array('Access-Control-Allow-Origin'=> '*'));
  json_decode($myresponse);
  return $myresponse;
}

  /**
   * @Route("/users", name="users")
   */
  public function amisAction(Request $request)
  {
    $factory = $this->get('security.encoder_factory');
    $fs = new Filesystem();
    $data = json_decode(file_get_contents('php://input'),true);
    $fs->dumpfile('/tmp/log.txt', 'yaaay');
    $id = $data['iduser'];

    $user=$this->getDoctrine()->getRepository('AppBundle\Entity\User')->find($id);

    if (!$user) {

      $response = array(
        'status' => 'failed',
        'id' => $data['iduser'],
        'error' => 'user doesnt exist',
        'token' => ''
      );
      $myresponse =new JsonResponse($response, 200, array('Access-Control-Allow-Origin'=> '*'));
      json_decode($myresponse);
      return $myresponse;
    }

    $serializer = $this->container->get('jms_serializer');

    $response = array(
             'status' => 'success',
             'username' => $user->getUsername(),
             'id'    => $user->getId(),
             'amis'  => $serializer->toArray($user->getAmis()),
             'users' => $this->lesMarcipilamis($user),
             'error' => '',
             'token' => ''
    );
    $myresponse =new JsonResponse($response, 200, array('Access-Control-Allow-Origin'=> '*'));
    json_decode($myresponse);
    return $myresponse;
  }
    /**
     * @Route("/ajouterAmi/{idAmi}", name="ajouterUnAmi")
     *
     *une remarque le generate url recupere le nom depuis routing.yml
     */
    public function ajouterAmiAction($idAmi='ajouterUnAmi')
    {
      $factory = $this->get('security.encoder_factory');
      $fs = new Filesystem();
//      $data = json_decode(file_get_contents('php://input'),true);
      $fs->dumpfile('/tmp/log.txt', 'yaaay');
      $serializer = $this->container->get('jms_serializer');

      $id = $_GET['iduser'];

      $user=$this->getDoctrine()->getRepository('AppBundle\Entity\User')->find($id);

      if (!$user) {

        $response = array(
          'status' => 'failed',
          'id' => $_GET['iduser'],
          'error' => 'user doesnt exist',
          'token' => ''
        );
        $myresponse =new JsonResponse($response, 200, array('Access-Control-Allow-Origin'=> '*'));
        json_decode($myresponse);
        return $myresponse;
      }

      $ami=$this->getDoctrine()->getRepository('AppBundle\Entity\User')->find($idAmi);
      $user->addAmi($ami);
      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($user);
      $entityManager->flush();
      $response = array(
              'status' => 'success',
              'username' => $user->getUsername(),
              'id' => $user->getId(),
              'amis' => $serializer->toArray($user->getAmis()),
              'users' => $this->lesMarcipilamis($user),
              'error' => '',
              'token' => ' '
     );
     $myresponse =new JsonResponse($response, 200, array('Access-Control-Allow-Origin'=> '*'));
     json_decode($myresponse);
     return $myresponse;
/*      $user = $this->getUser();

      if (!is_object($user) || !$user instanceof UserInterface) {
          throw new AccessDeniedException('This user does not have access to this section.');
      }


      if ($idAmi=='ajouterUnAmi') {
          throw new NotFoundHttpException('Sorry this id does not exist!');;
      }
      $ami=$this->getDoctrine()->getRepository('AppBundle\Entity\User')
      ->find($idAmi);
      $user->addAmi($ami);
      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($user);
      $entityManager->flush();
      //$url = $this->generateUrl('app');
    //  return $this->redirect($url);*/
    }

    /**
     * @Route("/supprimerAmi/{idAmi}", name="supprimerUnAmi")
     *
     *une remarque le generate url recupere le nom depuis routing.yml
     */
    public function supprimerAmiAction($idAmi='')
    {

      $user = $this->getDoctrine()->getRepository('AppBundle\Entity\User')->find($_GET['iduser']);
      $serializer = $this->container->get('jms_serializer');

      if (!is_object($user) || !$user instanceof UserInterface) {
         throw new AccessDeniedException('This user does not have access to this section.');
      }

      $ami=$this->getDoctrine()->getRepository('AppBundle\Entity\User')
      ->find($idAmi);
      $user->removeAmi($ami);
      $entityManager = $this->getDoctrine()->getManager();
      $entityManager->persist($user);
      $entityManager->flush();

      $response = array(
              'status' => 'success',
              'username' => $user->getUsername(),
              'id' => $user->getId(),
              'amis' => $serializer->toArray($user->getAmis()),
              'users' => $this->lesMarcipilamis($user),
              'error' => '',
              'token' => ' '
     );
     $myresponse =new JsonResponse($response, 200, array('Access-Control-Allow-Origin'=> '*'));
     json_decode($myresponse);
     return $myresponse;
    //      $url = $this->generateUrl('app');
    //      return $this->redirect($url);
    }

    /**
    * recupere tous les marcipulamis
    * qui ne sont pas des amis de l user a regler cq apres
    */
    private function lesMarcipilamis($user)
    {
      //recupere les id des amis-
      $tab=[];
      $tab[]=-10;

      foreach($user->getAmis() as $value)
        $tab[] = $value->getId();


      $em = $this->getDoctrine()->getManager();
      $qb = $em->createQueryBuilder();
      $users = $em->createQueryBuilder()
        ->select('u')
        ->from('AppBundle\Entity\User', 'u')
        ->where('u.id<>:idlogger')
        ->andwhere('u.id not in (:listeAmis)')
        ->getQuery()
        ->setParameter('idlogger',$user->getId())
        ->setParameter('listeAmis',$tab)
        ->getArrayResult();

      return $users;
    }

    /**
     *
     * @Route("/myprofile", name="profile")
     *
     */
    public function showAction()
    {
        $factory = $this->get('security.encoder_factory');
        $fs = new Filesystem();
        $data = json_decode(file_get_contents('php://input'),true);
        $fs->dumpfile('/tmp/log.txt', 'yaaay');
        $user = $this->getDoctrine()->getRepository('AppBundle\Entity\User')->find($_GET['iduser']);
        if (!is_object($user) || !$user instanceof UserInterface || !$user) {

            $response = array(
              'status' => 'failed',
              'username' => $data['pseudo'],
              'error' => 'problem with the backend or the user doesnt exist',
              'token' => ''
            );
            $myresponse =new JsonResponse($response, 200, array('Access-Control-Allow-Origin'=> '*'));
            json_decode($myresponse);
            return $myresponse;
        }
        $serializer = $this->container->get('jms_serializer');

        $response= array('status' =>'success' ,
                          'profile'=>$serializer->toArray($user),
                          'error'=>'',
                          'token'=>''
        );
        $myresponse=new JsonResponse($response,200,array('Access-Control-allow-origin' =>'*'));
        return $myresponse;
    }

    /**
    *
    *@Route("/modifprofile", name="modifprofile")
    *
    */
    public function updateProfileAction()
    {

        $factory = $this->get('security.encoder_factory');
        $fs = new Filesystem();
        $data = json_decode(file_get_contents('php://input'),true);
        $fs->dumpfile('/tmp/log.txt', 'yaaay');
        $userNvInfos=$data['NvProfil'];

        $user = $this->getDoctrine()->getRepository('AppBundle\Entity\User')->find($data['iduser']);
        if (!is_object($user) || !$user instanceof UserInterface || !$user) {
            $response = array(
              'status' => 'failed',
              'username' => $data['pseudo'],
              'error' => 'problem with the backend or the user doesnt exist',
              'token' => ''
            );
            $myresponse =new JsonResponse($response, 200, array('Access-Control-Allow-Origin'=> '*'));
            json_decode($myresponse);
            return $myresponse;
        }
        $user->setAge($userNvInfos['age']);
        $user->setFamille($userNvInfos['famille']);
        $user->setRace($userNvInfos['race']);
        $user->setNourriture($userNvInfos['nourriture']);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $serializer = $this->container->get('jms_serializer');

        $response= array('status' =>'success' ,
                          'profile'=>$serializer->toArray($user),
                          'error'=>'',
                          'token'=>''
        );
        $myresponse=new JsonResponse($response,200,array('Access-Control-allow-origin' =>'*'));
        return $myresponse;
    }

}
