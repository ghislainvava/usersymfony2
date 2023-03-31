<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Email;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity("email", message: "Cet email est déjà utilisé")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[NotBlank(message: "Vous devez saisir un nom d'utilisateur.")]
    private $username;

    #[ORM\Column(type: 'string', length: 255)]
    #[Length(min: 8, minMessage: "Votre mot de passe doit posséder au moins {{ limit }} caractères")]
    #[EqualTo(propertyPath: "confirm_password", message: "Vous n'avez donné le même mot de passe ")]
    private $password;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\Length(min: 2, max: 180)]
    #[Email(message: "Vous devez saisir un nom email valide.")]
    private $email;

    #[ORM\Column(type: 'json')]
    private array $roles;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Task::class)]
    private Collection $author;

    public function __construct()
    {
        $this->author = new ArrayCollection();
        $this->roles = ['ROLE_USER'];
    }

    public function getId()
    {
        return $this->id;
    }


    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getSalt()
    {
        return null;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
    public function eraseCredentials()
    {
    }

    /**
     * @return Collection<int, Task>
     */
    public function getAuthor(): Collection
    {
        return $this->author;
    }

    public function addAuthor(Task $author): self
    {
        if (!$this->author->contains($author)) {
            $this->author->add($author);
            $author->setUser($this);
        }

        return $this;
    }

    public function removeAuthor(Task $author): self
    {
        if ($this->author->removeElement($author)) {
            // set the owning side to null (unless already changed)
            if ($author->getUser() === $this) {
                $author->setUser(null);
            }
        }

        return $this;
    }
}
