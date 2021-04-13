<?php

use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *     tags={
 *          @OA\Tag(name="Authentication", x={"groups":{"publicV1", "publicV2", "private"}}),
 *          @OA\Tag(name="Checkout Client", x={"groups":{"publicV1", "publicV2", "private"}}),
 *          @OA\Tag(name="Checkout Server", x={"groups":{"publicV1", "publicV2", "private"}}),
 *          @OA\Tag(name="Back-end Order Creation", x={"groups":{"publicV1", "publicV2", "private"}}),
 *          @OA\Tag(name="Order Management", x={"groups":{"publicV1", "publicV2", "private"}}),
 *          @OA\Tag(name="Invoice Management", x={"groups":{"private"}}),
 *          @OA\Tag(name="Webhooks", x={"groups":{"publicV1", "private"}}),
 *
 *          @OA\Tag(name="Dashboard Orders", x={"groups":{"private"}}),
 *          @OA\Tag(name="Dashboard Debtors", x={"groups":{"private"}}),
 *          @OA\Tag(name="Dashboard Payments", x={"groups":{"private"}}),
 *          @OA\Tag(name="Dashboard Users", x={"groups":{"private"}}),
 *          @OA\Tag(name="Dashboard Merchants", x={"groups":{"private"}}),
 *
 *          @OA\Tag(name="Support", x={"groups":{"private"}}),
 *          @OA\Tag(name="API Docs", x={"groups":{"private"}}),
 *     }
 * )
 */
