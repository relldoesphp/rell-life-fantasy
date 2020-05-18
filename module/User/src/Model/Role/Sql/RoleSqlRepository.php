<?php

namespace User\Model\Permission\Sql;

use User\Model\Role\RoleRepositoryInterface;
use User\Model\User\User;
use User\Model\Role\Role;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\ResultSet\HydratingResultSet;

class RoleSqlRepository implements RoleRepositoryInterface
{
    private $db;
    private $hydrator;
    private $rolePrototype;

    public function __construct(
        AdapterInterface $db,
        HydratorInterface $hydrator,
        Role $rolePrototype
    ){
        $this->db = $db;
        $this->hydrator = $hydrator;
        $this->rolePrototype = $rolePrototype;
    }

    /**
     * @return mixed
     */
    public function findAllRoles()
    {
        $sql    = new Sql($this->db);
        $select = $sql->select('role', 'r')
            ->join(
                ['parent' => 'role_hierarchy'],        // join table with alias
                'parent.child_role_id = r.id'  // join expression
            )
            ->join(
                ['child' => 'role_hierarchy'],        // join table with alias
                'child.parent_role_id = r.id'  // join expression
            );
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->userPrototype);
        $resultSet->initialize($result);
        return $resultSet;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findRoleById($id)
    {
        // TODO: Implement findRoleById() method.
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function findRolesForUser(User $user)
    {
        // TODO: Implement findRolesForUser() method.
    }

}