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
     * Insets a new user in DB
     *
     * @Rest\Post("/newUser")
     * @return string
     */
    public function postAction()
    {
        $data = new User;
        $name = $_POST['user'];
        $pass = $_POST['pass'];
        if(empty($name) || empty($pass))
        {
            return "Parameters were empty";
        }
        $data->setName($name);
        $data->setPassword($pass);
        $em = $this->getDoctrine()->getManager();
        $em->persist($data);
        $em->flush();
        return "User Added Successfully";
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
        if(empty($name))
        {
            return"'name' parameter was empty";
        }
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepository->findBy(array('name' => $name));
        //check returned object
        if (!is_null($user) && !empty($user)) {
            return "User with name '".$name."' exists";
        }
        return "User with name '".$name."' does NOT exist";
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
        //initial parameter validations
        if(empty($name) || empty($pass))
        {
            return"'name' or 'pass' parameter was empty";
        }
        if ($this->checkUserPassMatch($name, $pass)) {
            return "The combination of user + password is CORRECT";
        }
        return "The combination of user + password is WRONG";
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
        //initial parameter validations
        if(empty($name) || empty($pass1) || empty($pass2))
        {
            return"'name', 'pass1' or 'pass2' parameter was empty";
        }
        //if correct combination, proceed
        if ($this->checkUserPassMatch($name, $pass1)) {
            $userRepository = $this->getDoctrine()->getRepository(User::class);
            $user = $userRepository->findOneBy(array('name' => $name, 'password' => $pass1));
            $user->setPassword($pass2);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return "Password of user '".$name."' UPDATED";
        }
        return "The combination of user + password is WRONG";
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