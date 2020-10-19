# Micro ODM
small object document mapper.

## Usage
At your endpoint
```php
    list($mongo, $database) = ApplicationDbContext::initialDbConnection();
    // binding it to DI container to reuse
    SimpleDi::bindingInstance(Database::class,$database);
    SimpleDi::bindingInstance(Client::class, $mongo);
```

Define Entity
```php
class ARandomEntity extends BaseEntity
{
    /**
     * if you want to igrnore this field,
     * just put @\MicroOdm\Annotations\UnPersist() to it
     * @UnPersist
     * @var array
     */
    private array $domainEvents = [];    
    protected ?int $iid;
    // for multi domain
    protected ?string $dmn;
    protected ?string $school;
    protected ?User $u;
    
    // for the nest object, must define full namespace
    // we will upgrade in next version
    /**
     * @return \Uni\Domains\Share\ObjectValues\User|null
     */
    public function getU(): ?User
    {
        return $this->u;
    }

    /**
     * @param \Uni\Domains\Share\ObjectValues\User|null $u
     */
    public function setU(?User $u): void
    {
        $this->u = $u;
    }

}
```


Get the Repository to interact with Db
```php
// at repository
class CreditRepository extends BaseEntityRepository
{
    public function __construct(Database $database)
    {
        // the entity class
        $entityClass = Credit::class;
        parent::__construct($entityClass, $database);
    }
}
```

