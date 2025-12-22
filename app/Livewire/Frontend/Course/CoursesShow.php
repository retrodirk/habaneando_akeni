<?php

namespace App\Livewire\Frontend\Course;

use Stripe\Exception\InvalidRequestException;
use Stripe\Checkout\Session as StripeCheckoutSession;
use App\Models\ClubRate;
use App\Traits\HasLogin;

use Livewire\Component;
use App\Models\Course;
use App\Models\CourseSubscription;
use App\Enum\SubscriptionTypeEnum;
use App\Enum\PaymentStatusEnum;
use App\Enum\PaymentMethodEnum;
use Stripe\Stripe;
use Carbon\Carbon;

class CoursesShow extends Component
{
    use HasLogin;

    public $course;
    public $header;
    public $price;
    public $quantityMen = 0;
    public $quantityWomen = 0;
    public $totalPrice;
    public $routePath;
    public $minClubPrice = 0;


    public function mount(Course $course)
    {
        $this->course = $course->load('subcategory.category', 'location');
        $this->price = $this->course->subcategory->amount;
        $this->calculateTotalPrice();
        $this->routePath = route('frontend.course.show', $course);
        $this->minClubPrice = ClubRate::where('status', true)->min('amount');

        if (auth()->guard('customer')->check()) {
            // Customer is logged in, return the customer
            $this->customer = auth()->guard('customer')->user();
        }
    }

    public function updatedQuantityMen()
    {
        $this->calculateTotalPrice();
    }

    public function updatedQuantityWomen()
    {
        $this->calculateTotalPrice();
    }

    private function calculateTotalPrice()
    {
        $this->totalPrice = ($this->quantityMen + $this->quantityWomen) * $this->price;
    }
    public function createSession()
    {
        $this->validateInput();

        if ($this->quantityMen == 0 && $this->quantityWomen == 0) {
            return;
        }

        try {
            $checkoutUrl = $this->createCheckoutSession();
            return redirect()->away($checkoutUrl);
        } catch (InvalidRequestException $e) {
            $this->addError('quantityMen', $e->getMessage());
        }
    }

    private function validateInput()
    {
        $rules = [
            'quantityMen' => 'required|integer|min:0',
            'quantityWomen' => 'required|integer|min:0',
        ];

        $messages = [
            'quantityMen.min' => 'Mindestens ein Teilnehmer ist erforderlich.',
            'quantityWomen.min' => 'Mindestens ein Teilnehmer ist erforderlich.',
            'quantityMen.required' => 'Mindestens ein Teilnehmer ist erforderlich.',
            'quantityWomen.required' => 'Mindestens ein Teilnehmer ist erforderlich.',
        ];

        $this->validate($rules, $messages);

        if ($this->quantityMen == 0 && $this->quantityWomen == 0) {
            $this->addError('quantityMen', 'Mindestens ein Teilnehmer ist erforderlich.');
        }
    }
    private function createCheckoutSession()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $appUrl = env('APP_URL');

        $checkout_session = StripeCheckoutSession::create([
            'payment_method_types' => ['card', 'sepa_debit'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $this->course->subcategory->category->name . ' ' . $this->course->name,
                    ],
                    'unit_amount' => $this->course->subcategory->amount * 100,
                ],
                'quantity' => $this->quantityMen + $this->quantityWomen,
            ]],
            'metadata' => [
                'customer_id' => $this->customer->id,
                'course_id' => $this->course->id,
                'quantityMen' => $this->quantityMen,
                'quantityWomen' => $this->quantityWomen
            ],
            'billing_address_collection' => 'required',
            'customer_email' => $this->customer->email,
            'mode' => 'payment',
            'locale' => 'de',
            'payment_intent_data' => [
                'description' => $this->getPaymentDescription(),
            ],
            // 'success_url' => $appUrl . '/checkout/course/success?session_id={CHECKOUT_SESSION_ID}',
            'success_url' => $appUrl . '/checkout/course/success/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $appUrl,
        ]);

        return $checkout_session->url;
    }

    private function getPaymentDescription(): string
    {
        if (!$this->course->start_date) {
            return $this->course->name;
        }

        $date = Carbon::parse($this->course->start_date)->format('d.m.Y');

        return $this->course->name . ' - ' . $date;
    }
    private function createSubscription($customer, $numberOfMen, $numberOfWomen, $amount)
    {
        return CourseSubscription::create([
            'customer_id' => $customer->id,
            'course_id' => $this->course->id,
            'student' => false,
            'numberOfMen' => $numberOfMen,
            'numberOfWomen' => $numberOfWomen,
            'clubMember' => false,
            'subscriptionType' => SubscriptionTypeEnum::SINGLE_PAYMENT,
            'amount' => $amount,
            'method' => PaymentMethodEnum::TRANSFER,
            'payment_status' => PaymentStatusEnum::PENDING
        ]);
    }
}
