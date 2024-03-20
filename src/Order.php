<?php

namespace Moudarir\Binga;

class Order
{

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $externalId;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $apiVersion;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var float
     */
    private $totalAmount;

    /**
     * @var float
     */
    private $stampDuty;

    /**
     * @var float
     */
    private $clientStampDuty;

    /**
     * @var float
     */
    private $serviceCharge;

    /**
     * @var float
     */
    private $clientServiceCharge;

    /**
     * @var string
     */
    private $bookUrl;

    /**
     * @var string
     */
    private $payUrl;

    /**
     * @var string
     */
    private $successUrl;

    /**
     * @var string
     */
    private $failureUrl;

    /**
     * @var string
     */
    private $buyerAddress;

    /**
     * @var string
     */
    private $buyerEmail;

    /**
     * @var string
     */
    private $buyerFirstName;

    /**
     * @var string
     */
    private $buyerLastName;

    /**
     * @var string
     */
    private $buyerPhone;

    /**
     * @var bool
     */
    private $archived;

    /**
     * @var bool
     */
    private $offline;

    /**
     * @var \DateTime
     */
    private $creationDate;

    /**
     * @var \DateTime
     */
    private $expirationDate;

    /**
     * @var \DateTime
     */
    private $modificationDate;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->setData($data);
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getExternalId(): string
    {
        return $this->externalId;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @return float
     */
    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    /**
     * @return float
     */
    public function getStampDuty(): float
    {
        return $this->stampDuty;
    }

    /**
     * @return float
     */
    public function getClientStampDuty(): float
    {
        return $this->clientStampDuty;
    }

    /**
     * @return float
     */
    public function getServiceCharge(): float
    {
        return $this->serviceCharge;
    }

    /**
     * @return float
     */
    public function getClientServiceCharge(): float
    {
        return $this->clientServiceCharge;
    }

    /**
     * @return string
     */
    public function getBookUrl(): string
    {
        return $this->bookUrl;
    }

    /**
     * @return string
     */
    public function getPayUrl(): string
    {
        return $this->payUrl;
    }

    /**
     * @return string
     */
    public function getSuccessUrl(): string
    {
        return $this->successUrl;
    }

    /**
     * @return string
     */
    public function getFailureUrl(): string
    {
        return $this->failureUrl;
    }

    /**
     * @return string
     */
    public function getBuyerAddress(): string
    {
        return $this->buyerAddress;
    }

    /**
     * @return string
     */
    public function getBuyerEmail(): string
    {
        return $this->buyerEmail;
    }

    /**
     * @return string
     */
    public function getBuyerFirstName(): string
    {
        return $this->buyerFirstName;
    }

    /**
     * @return string
     */
    public function getBuyerLastName(): string
    {
        return $this->buyerLastName;
    }

    /**
     * @return string
     */
    public function getBuyerPhone(): string
    {
        return $this->buyerPhone;
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return $this->archived;
    }

    /**
     * @return bool
     */
    public function isOffline(): bool
    {
        return $this->offline;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate(): ?\DateTime
    {
        return $this->creationDate;
    }

    /**
     * @return \DateTime
     */
    public function getModificationDate(): ?\DateTime
    {
        return $this->modificationDate;
    }

    /**
     * @return \DateTime
     */
    public function getExpirationDate(): ?\DateTime
    {
        return $this->expirationDate;
    }

    /**
     * @param string $code
     * @return self
     */
    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @param string $externalId
     * @return self
     */
    public function setExternalId(string $externalId): self
    {
        $this->externalId = $externalId;
        return $this;
    }

    /**
     * @param string $id
     * @return self
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $status
     * @return self
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param string $apiVersion
     * @return self
     */
    public function setApiVersion(string $apiVersion): self
    {
        $this->apiVersion = $apiVersion;
        return $this;
    }

    /**
     * @param string $amount
     * @return self
     */
    public function setAmount(string $amount): self
    {
        $this->amount = (float)$amount;
        return $this;
    }

    /**
     * @param string $totalAmount
     * @return self
     */
    public function setTotalAmount(string $totalAmount): self
    {
        $this->totalAmount = (float)$totalAmount;
        return $this;
    }

    /**
     * @param string $stampDuty
     * @return self
     */
    public function setStampDuty(string $stampDuty): self
    {
        $this->stampDuty = (float)$stampDuty;
        return $this;
    }

    /**
     * @param string $clientStampDuty
     * @return self
     */
    public function setClientStampDuty(string $clientStampDuty): self
    {
        $this->clientStampDuty = (float)$clientStampDuty;
        return $this;
    }

    /**
     * @param string $serviceCharge
     * @return self
     */
    public function setServiceCharge(string $serviceCharge): self
    {
        $this->serviceCharge = (float)$serviceCharge;
        return $this;
    }

    /**
     * @param string $clientServiceCharge
     * @return self
     */
    public function setClientServiceCharge(string $clientServiceCharge): self
    {
        $this->clientServiceCharge = (float)$clientServiceCharge;
        return $this;
    }

    /**
     * @param string $bookUrl
     * @return self
     */
    public function setBookUrl(string $bookUrl): self
    {
        $this->bookUrl = $bookUrl;
        return $this;
    }

    /**
     * @param string $payUrl
     * @return self
     */
    public function setPayUrl(string $payUrl): self
    {
        $this->payUrl = $payUrl;
        return $this;
    }

    /**
     * @param string $successUrl
     * @return self
     */
    public function setSuccessUrl(string $successUrl): self
    {
        $this->successUrl = $successUrl;
        return $this;
    }

    /**
     * @param string $failureUrl
     * @return self
     */
    public function setFailureUrl(string $failureUrl): self
    {
        $this->failureUrl = $failureUrl;
        return $this;
    }

    /**
     * @param string $buyerAddress
     * @return self
     */
    public function setBuyerAddress(string $buyerAddress): self
    {
        $this->buyerAddress = $buyerAddress;
        return $this;
    }

    /**
     * @param string $buyerEmail
     * @return self
     */
    public function setBuyerEmail(string $buyerEmail): self
    {
        $this->buyerEmail = $buyerEmail;
        return $this;
    }

    /**
     * @param string $buyerFirstName
     * @return self
     */
    public function setBuyerFirstName(string $buyerFirstName): self
    {
        $this->buyerFirstName = $buyerFirstName;
        return $this;
    }

    /**
     * @param string $buyerLastName
     * @return self
     */
    public function setBuyerLastName(string $buyerLastName): self
    {
        $this->buyerLastName = $buyerLastName;
        return $this;
    }

    /**
     * @param string $buyerPhone
     * @return self
     */
    public function setBuyerPhone(string $buyerPhone): self
    {
        $this->buyerPhone = $buyerPhone;
        return $this;
    }

    /**
     * @param string $archived
     * @return self
     */
    public function setArchived(string $archived): self
    {
        $this->archived = $archived === 'true';
        return $this;
    }

    /**
     * @param string $offline
     * @return self
     */
    public function setOffline(string $offline): self
    {
        $this->offline = $offline === 'true';
        return $this;
    }

    /**
     * @param string $creationDate
     * @return self
     */
    public function setCreationDate(string $creationDate): self
    {
        $this->creationDate = Utils::toDatetime($creationDate);
        return $this;
    }

    /**
     * @param string $modificationDate
     * @return self
     */
    public function setModificationDate(string $modificationDate): self
    {
        $this->modificationDate = Utils::toDatetime($modificationDate);
        return $this;
    }

    /**
     * @param string $expirationDate
     * @return self
     */
    public function setExpirationDate(string $expirationDate): self
    {
        $this->expirationDate = Utils::toDatetime($expirationDate);
        return $this;
    }

    /**
     * @param array $data
     * @return void
     */
    private function setData(array $data): void
    {
        if (!empty($data)) {
            foreach ($data as $property => $value) {
                if (\property_exists($this, $property) && \method_exists($this, $property)) {
                    $this->$property($value);
                }
            }
        }
    }
}
