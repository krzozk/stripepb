<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('hello');
});

Route::get('pay', function(){
    return View::make('stripe');
});

Route::post('pay', function(){
    // Use the config for the stripe secret key
    //Stripe::setApiKey(Config::get('stripe.secret'));
    Stripe::setApiKey('sk_test_qBHocDAhVJ0FQDIieaT7D6w5');

    // Get the credit card details submitted by the form
    $token = Input::get('stripeToken');

    // Create the charge on Stripe's servers - this will charge the user's card
    try {
        $charge = Stripe_Charge::create(array(
                "amount" => 2000, // amount in cents
                "currency" => "usd",
                "card"  => $token,
                "description" => 'Charge for my product')
        );
    }catch(Stripe_CardError $e)
    {
        $body = $e->getJsonBody();
        $err = $body['error'];
        Log::write('error', 'Stripe: ' . $err['type'] . ': ' . $err['code'] . ': ' . $err['message']);
        $error = $err['message'];
    }
    catch (Stripe_InvalidRequestError $e)
    {
        $body = $e->getJsonBody();
        $err = $body['error'];
        Log::write('error', 'Stripe: ' . $err['type'] . ': ' . $err['message']);
        $error = $err['message'];
    }
    catch (Stripe_ApiConnectionError $e)
    {
        // Network communication with Stripe failed
        $error = 'A network error occurred.';
    }
    catch (Stripe_AuthenticationError $e)
    {
        Log::write('error','Stripe: API key rejected!', 'stripe');
        $error = 'Payment processor API key error.';
    }
    catch (Stripe_Error $e)
    {
        Log::write('error', 'Stripe: Stripe_Error - Stripe could be down.');
        $error = 'Payment processor error, try again later.';

    }
    catch (Exception $e)
    {
        Log::write('error', 'Stripe: Unknown error.');
        $error = 'There was an error, try again later.';
    }

    if ($error !== null)
    {
        throw new Stripe_Exception( (string) $error);
    }
/*
    } catch(Stripe_CardError $e) {
        $e_json = $e->getJsonBody();
        $error = $e_json['error'];
        // The card has been declined
        // redirect back to checkout page
        return Redirect::to('pay')
            ->withInput()->with('stripe_errors',$error['message']);
    }
*/
    // Maybe add an entry to your DB that the charge was successful, or at least Log the charge or errors
    // Stripe charge was successfull, continue by redirecting to a page with a thank you message
    return Redirect::to('pay/success');
});

//insertamos un nuevo producto en el carrito
Route::post("insert", function(){

    $item = array(
        'id' => Input::get("id"),
        'qty' => Input::get("qty"),
        'price' => Input::get("price"),
        'name' => Input::get("name")
    );

    //add options to row
    $item["options"] = array("color" => "orange", "avaliable" => "yes");

    //add row to cart
    if(Simplecart::insert($item))
    {
        return Redirect::to("show");
    }
});

//con esto podemos actualizar el carrito
Route::post("update", function(){
    $update = array(
        'id' => Input::get("id"),
        'rowid' => Input::get("rowid"),
        'qty' => Input::get("qty"),
        'price' => Input::get("price"),
        'name' => Input::get("name")
    );

    $update["options"] = array("color" => "orange", "avaliable" => "yes");

    if(Simplecart::update($update))
    {
        return Redirect::to("show");
    }
});

//mostramos el carrito con los productos
Route::get("show", function()
{
    $cart = Simplecart::get_content();
    $totalcart = Simplecart::total_cart();
    $totalitems = Simplecart::total_articles();
    return View::make("cart", array("cart" => $cart, "total_cart" => $totalcart, "total_items" => $totalitems));
});

//eliminamos una fila(rowid) completa
Route::get("remove/{rowid}", function($rowid)
{
    if(Simplecart::remove_item($rowid))
    {
        return Redirect::to("show");
    }

});

//vaciamos el carrito
Route::get("destroy", function()
{
    if(Simplecart::destroy())
    {
        return Redirect::to("show");
    }
});


Route::get("boton", function()
{

        return View::make("boton");

});


Route::get("prueba", function()
{

    Stripe::setApiKey('sk_test_qBHocDAhVJ0FQDIieaT7D6w5');
    $myCard = array('number' => '4242424242424242', 'exp_month' => 5, 'exp_year' => 2015);
    $charge = Stripe_Charge::create(array('card' => $myCard, 'amount' => 1000, 'currency' => 'usd'));

    return View::make("prueba", array("charge" => $charge ));

});


Route::post("paystripe", function()
{

    Stripe::setApiKey('sk_test_qBHocDAhVJ0FQDIieaT7D6w5');
    $myCard = array('number' => '4242424242424242', 'exp_month' => 5, 'exp_year' => 2015);
    $charge = Stripe_Charge::create(array('card' => $myCard, 'amount' => 1000, 'currency' => 'usd'));

    return View::make("prueba", array("charge" => $charge ));

});