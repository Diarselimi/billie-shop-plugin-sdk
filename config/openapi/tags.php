<?php

use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *     tags={
 *          @OA\Tag(name="Authentication", x={"groups":{"standard", "checkout-server", "dashboard", "support"}}),
 *          @OA\Tag(name="Order Creation", x={"groups":{"standard", "support", "checkout-client", "checkout-server"}}),
 *          @OA\Tag(name="Order Pre-approval", x={"groups":{"standard"}}),
 *          @OA\Tag(name="Order Management", x={"groups":{"standard", "dashboard", "support", "salesforce", "checkout-server"}}),
 *          @OA\Tag(name="Payments", x={"groups":{"dashboard"}}),
 *          @OA\Tag(name="Debtors", x={"groups":{"dashboard", "support", "salesforce"}}),
 *          @OA\Tag(name="Merchants", x={"groups":{"support"}}),
 *          @OA\Tag(name="Webhooks", x={"groups":{"standard", "checkout-server", "support"}}),
 *          @OA\Tag(name="Misc.", x={"groups":{"support", "salesforce"}}),
 *     }
 * )
 */
