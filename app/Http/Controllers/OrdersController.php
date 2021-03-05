<?php

namespace App\Http\Controllers;

use Illuminate\Support\{Str, Arr};

use App\Models\{Order, States, Client, Address, Cities};

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Auth;

use Exception;

use TeamPickr\DistanceMatrix\Licenses\StandardLicense;

use TeamPickr\DistanceMatrix\DistanceMatrix;

class OrdersController extends Controller {

    private $orderAttributes;

    private $clientTypes;

    private $rateCard;

    private $expressRateCard;

    function __construct() {

        $this->orderAttributes = ['description', 'weight', 'quantity', 'item_worth', 'fragile', 'express_delivery'];

        $this->clientTypes = ["sender", "receiver"];

        $this->rateCard = [
            19 => 1000, 29 => 1500, 39 => 1800, 49 => 2000,

            59 => 2500, 75 => 3000, 89 => 3500, 109 => 4000,

            120 => 4500
        ];

        $this->expressRateCard = [
            20 => 1000, 50 => 1500, 80 => 2000, 120 => 2500
        ];
    }

    public function create() {

        $order = new Order(["billing_number" => Str::random(6)]);

        foreach($this->orderAttributes as $attribute)

            $order->$attribute = null;

        $dummyClient = new Client();

        foreach($this->clientTypes as $client)

            $order->$client = $dummyClient;

        $states = States::with(["cities"])->get();

        $clientModes = $this->getClientModes($order);

        $postback_url = "/orders";

        return view("orders.new-order", compact("states", "order", "clientModes", "postback_url"));
    }

    public function store(Request $request) {

        return DB::transaction(function () use ($request) {

            $relations = ["agent_id" => Auth::id()];

            $clients = [];

            $orderRequest = $request->order;

            foreach ($this->clientTypes as $type) {

                $typePayload = $orderRequest[$type];

                $typePayload["address_id"] = $this->createAddress($typePayload)->id;

                $clients[$type] = Client::create($typePayload);

                $relations[$type ."_id"] = $clients[$type]->id;
            }
            $this->orderAttributes[] = "billing_number";

            $newOrderFields = Arr::only($orderRequest, $this->orderAttributes) + $relations;

            $distance = $this->getDistance($clients);

            $newOrderFields["price"] =  $this->updateExpressPrice(
                $this->getDistanceAmount($distance),

                $distance, $newOrderFields
            );

            $order = Order::create($newOrderFields);

            return redirect("/orders/" . $order->id);
        });
    }

    public function show (Order $order) {

        $this->loadFullOrder($order);

        $clientModes = $this->getClientModes($order);

        return view("orders.order-preview", compact("order", "clientModes"));
    }

    // returns 2km when unable to calculate the given locations
    private function getDistance(array $clients):int {

        $newAddresses = array_map(function($context) {

            $address = $context->address->name;

            $city = $context->address->city->name;

            $state = $context->address->city->state->name;;

            return "$address, $city, $state";
        }, $clients);

        $license = new StandardLicense(env("GOOGLE_KEY"));

        $rows = (new DistanceMatrix($license))

        ->addOrigin("{$newAddresses["sender"]}")

        ->addDestination("{$newAddresses["receiver"]}")

        ->request()->rows();

        if ($distance = $rows[0]->elements()[0]->distance())

            return $distance/1000;

        return 2;
    }

    private function getClientModes(Order $order) {

        return [
            "sender" => $order->sender,

            "receiver" => $order->receiver
        ];
    }

    // return closest km that tallies with given distance
    private function closestToDistance(int $distance, array $range):int {

        $allGreater = array_filter($range, function ($km) use ($distance) {

            return $km > $distance;
        }, ARRAY_FILTER_USE_KEY );

        if (!empty($allGreater))

            return current($allGreater);

        return end($range);
    }

    private function getDistanceAmount(int $distance):int {

        return $this->closestToDistance($distance, $this->rateCard);
    }

    private function getExpressAmount(int $distance):int {

        return $this->closestToDistance($distance, $this->expressRateCard);
    }

    public function edit( Order $order) {

        if (!$order->approved) {

            $this->loadFullOrder($order);

            $states = States::with(["cities"])->get();

            $clientModes = $this->getClientModes($order);

            $postback_url = "/orders/{$order->id}";

            return view("orders.new-order", compact("states", "order", "clientModes", "postback_url"));
        }

        return "Unable to edit approved order";
    }

    public function update(Request $request, Order $order) {

        $order->load([ "sender","receiver" ]);

        $orderRequest = $request->order;

        foreach ($this->clientTypes as $type) {

            $clientData = $orderRequest[$type];

            $client = $order->$type;

            $client->address()->delete(); //we'll assume order was changed and just create a new one :D

            $clientData["address_id"] = $this->createAddress($clientData)->id;

            $client->update($clientData);
        }

        $order = $order->refresh();

        $clients = $this->getClientModes($order);

        $orderData = Arr::only($orderRequest, $this->orderAttributes);

        $distance = $this->getDistance($clients);

        $orderData["price"] =  $this->updateExpressPrice(
            $this->getDistanceAmount($distance), // should the value be deducted?

            $distance, $orderData
        );

        $orderData = $this->updateCheckboxes($orderData);

        $order->update($orderData);

        return redirect("/orders/" . $order->id);
    }

    private function createAddress(array $clientData): Address {

        $addressValue = Arr::pull($clientData, "address");

        return Address::create(
            Arr::except($addressValue, ["state"])
        );
    }

    public function printPreview(Order $order) {

        $order->update(["approved" => 1]);

        $this->loadFullOrder($order);

        $clientModes = $this->getClientModes($order);

        return view("orders.print-preview", compact("order", "clientModes"));
    }

    private function loadFullOrder(Order $order) {

        $order->load([
            "sender.address.city.state",
            "receiver.address.city.state"
        ]);
    }

    private function updateExpressPrice(int $currentPrice, int $distance, array $payload):int {

        if (array_key_exists("express_delivery", $payload)) {

            return $currentPrice += $this->getExpressAmount($distance);
        }
        return $currentPrice;
    }

    // since this field points to a checkbox, it won't be set if it's unchecked
    private function updateCheckboxes(array $payload):array {

        foreach (["fragile", "express_delivery"] as $column)

            if (!array_key_exists($column, $payload))

                $payload[$column] = 0;

        return $payload;
    }
}

?>
