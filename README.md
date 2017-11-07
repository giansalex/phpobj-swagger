# PHP Object - SWAGGER

PHP Objects to Swagger Definitions (Example)

## Php object

```php
class Document
{
    /**
     * @var string
     */
    private $tipoDoc;

    /**
     * @var string
     */
    private $nroDoc;

    /**
     * @return string
     */
    public function getTipoDoc()
    {
        return $this->tipoDoc;
    }

    /**
     * @param string $tipoDoc
     * @return Document
     */
    public function setTipoDoc($tipoDoc)
    {
        $this->tipoDoc = $tipoDoc;
        return $this;
    }

    /**
     * @return string
     */
    public function getNroDoc()
    {
        return $this->nroDoc;
    }

    /**
     * @param string $nroDoc
     * @return Document
     */
    public function setNroDoc($nroDoc)
    {
        $this->nroDoc = $nroDoc;
        return $this;
    }
}
```

## Swagger Definition
```yml
definitions:
  Document:
    type: object
    properties:
      tipoDoc:
        type: string
      nroDoc:
        type: string
```
