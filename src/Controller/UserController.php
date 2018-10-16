<?php


namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class UserController extends Controller
{

    /**
     * Checks if the specified user exists in DB or not
     *
     * @Rest\Get("/existsUser/{name}")
     *
     * @param   $name
     * @return string
     */
    public function getExistsUserAction($name)
    {
        $response = null;
        if(empty($name))
        {
            $response = new Response("'name' parameter was empty", Response::HTTP_BAD_REQUEST);
        }
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepository->findBy(array('name' => $name));
        //check returned object
        if (!is_null($user) && !empty($user)) {
            $response = new Response("User with name '".$name."' exists", Response::HTTP_OK);
        }
        else {
            $response = new Response("User with name '".$name."' does NOT exist", Response::HTTP_BAD_REQUEST);
        }
        return $response;
    }

    /**
     * Checks if the combination of user + password matches in DB
     *
     * @Rest\Get("/matchUserPass/{name}/{pass}")
     *
     * @param $name
     * @param $pass
     * @return string
     */
    public function getMatchUserPassAction($name, $pass)
    {
        $response = null;
        //initial parameter validations
        if(empty($name) || empty($pass))
        {
            $response = new Response("'name' or 'pass' parameter was empty", Response::HTTP_BAD_REQUEST);
        }
        if ($this->checkUserPassMatch($name, $pass)) {
            $response = new Response("The combination of user + password is CORRECT", Response::HTTP_OK);
        }
        else {
            $response = new Response("The combination of user + password is WRONG", Response::HTTP_BAD_REQUEST);
        }
        return $response;
    }

    /**
     * Updates the password of the specified user with pass2, if pass1 is his actual password
     *
     * @Rest\Put("/updateUserPass/{name}/{pass1}/{pass2}")
     *
     * @param $name
     * @param $pass1
     * @param $pass2
     * @return string
     */
    public function putUserPassAction($name, $pass1, $pass2)
    {
        $response = null;
        //initial parameter validations
        if(empty($name) || empty($pass1) || empty($pass2))
        {
            $response = new Response("'name', 'pass1' or 'pass2' parameter was empty", Response::HTTP_BAD_REQUEST);
        }
        if ($this->checkUserPassMatch($name, $pass1)) {
            if ($pass1 === $pass2) {
                $response = new Response("The new password has to be different", Response::HTTP_BAD_REQUEST);
            }
            else {
                //do the update
                $userRepository = $this->getDoctrine()->getRepository(User::class);
                $user = $userRepository->findOneBy(array('name' => $name, 'password' => $pass1));
                $user->setPassword($pass2);
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                $response = new Response("Password of user '" . $name . "' UPDATED", Response::HTTP_OK);
            }
        }
        else {
            $response = new Response("The combination of user + password is WRONG", Response::HTTP_BAD_REQUEST);
        }
        return $response;
    }




    //--------------------------------------------------------------------------
    //------------------------------ADDITIONAL METHODS-------------------------
    //--------------------------------------------------------------------------


    /**
     * Insets a new user in DB
     *
     * @Rest\Post("/newUser")
     * @return string
     */
    public function postAction()
    {
        $response = null;
        $data = new User;
        $name = $_POST['user'];
        $pass = $_POST['pass'];
        if(empty($name) || empty($pass))
        {
            $response = new Response("Parameters were empty", Response::HTTP_BAD_REQUEST);
        }
        else {
            $data->setName($name);
            $data->setPassword($pass);
            $em = $this->getDoctrine()->getManager();
            $em->persist($data);
            $em->flush();
            $response = new Response("User Added Successfully", Response::HTTP_OK);
        }
        return $response;
    }

    /**
     * Returns all users in DB
     *
     * @Rest\Get("/users")
     *
     * * @return string
     */
    public function getAllUsersAction()
    {
        $restResult = $this->getDoctrine()->getRepository(User::class)->findAll();
        //serializing result into json
        $jsonContent = $this->jsonEncode($restResult);
        return $jsonContent;
    }

    /**
     * Returns the user (if exists) with the specified name
     *
     * @Rest\Get("/user/{name}")
     *
     * @return string
     */
    public function getUserAction($name)
    {
        if(empty($name))
        {
            return "'name' parameter was empty";
        }
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $restResult = $userRepository->findBy(array('name' => $name));
        //serializing result into json
        $jsonContent = $this->jsonEncode($restResult);
        return $jsonContent;
    }




    //--------------------------------------------------------------------------
    //-------------------------------PRIVATE METHODS---------------------------
    //--------------------------------------------------------------------------

    /**
     * Encodes input into json
     *
     * @param $param
     * @return string
     */
    private function jsonEncode($param)
    {
        $jsonContent = null;
        if (!is_null($param)) {
            $serializer = new Serializer(array(new ObjectNormalizer()), array(new XmlEncoder(), new JsonEncoder()));
            $jsonContent = $serializer->serialize($param, 'json');
        }
        return $jsonContent;
    }

    /**
     * Checks if the combination user + password exists in DB
     * returns true if YES, false if NOT
     *
     * @param $user
     * @param $pass
     * @return bool
     */
    private function checkUserPassMatch($user, $pass)
    {
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepository->findBy(array('name' => $user, 'password' => $pass));
        //check returned object
        if (!is_null($user) && !empty($user)) {
            return true;
        }
        return false;
    }
}