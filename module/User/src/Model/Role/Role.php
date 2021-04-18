<?php


namespace User\Model\Role;


class Role
{
    protected $id;

    protected $name;

    protected $description;

    protected $dateCreated;

    private $parentRoles;

    protected $childRoles;

    private $permissions;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->parentRoles = new ArrayCollection();
        $this->childRoles = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    /**
     * Returns role ID.
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets role ID.
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    public function getParentRoles()
    {
        return $this->parentRoles;
    }

    public function getChildRoles()
    {
        return $this->childRoles;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function addParent($role)
    {
        if ($this->getId() == $role->getId()) {
            return false;
        }

        if (!$this->hasParent($role)) {
            $this->parentRoles->add($role);
            $role->getChildRoles()->add($this);
            return true;
        }

        return false;
    }

    /**
     * Clear parent roles
     */
    public function clearParentRoles()
    {
        $this->parentRoles = [];
    }

    /**
     * Check if parent role exists
     * @param Role $role
     * @return bool
     */
    public function hasParent(Role $role)
    {
        if ($this->getParentRoles()->contains($role)) {
            return true;
        }

        return false;
    }
}