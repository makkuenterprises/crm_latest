<?php

namespace App\Http\Controllers\Admin;

use DB;
use Storage;
use Carbon\Carbon;
use App\Models\Bill;
use App\Models\Lead;
use App\Models\Plan;
use App\Models\Admin;
use App\Models\Group;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Service;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Password;
use App\Models\CrmSetting;
use Illuminate\Http\Request;
use App\Models\CompanyDetail;
use App\Models\DomainHosting;
use App\Models\Role;
use App\Models\LeadsManager;
use App\Models\MailCredential;
use App\Models\PaymentSetting;
use App\Models\ServiceCategory;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;



/*
|--------------------------------------------------------------------------
| Admin Update Controller
|--------------------------------------------------------------------------
|
| Update operations for admin are handled from this controller.
|
*/

interface AdminUpdate
{
    public function handleAccountInformationUpdate(Request $request);
    public function handleAccountPasswordUpdate(Request $request);
    public function handleEmployeeUpdate(Request $request, $id);
    public function handleGroupUpdate(Request $request, $id);
    public function handleRoleUpdate(Request $request, $id);
    public function handleLeadsManagerUpdate(Request $request, $id);
    public function handleCampaignUpdate(Request $request, $id);
    public function handleCustomerUpdate(Request $request, $id);
    public function handleProjectUpdate(Request $request, $id);
    public function handlePaymentUpdate(Request $request, $id);
    public function handleCompanyDetailsUpdate(Request $request);
    public function handlePaymentSettingUpdate(Request $request);
    public function handleMailCredentialsUpdate(Request $request);
    public function handleBillUpdate(Request $request, $id);
    public function handleAdminUpdate(Request $request, $id);
    public function handleDomainHostingUpdate(Request $request, $id);
    public function handlePlanUpdate(Request $request, $id);
    public function handlePackageUpdate(Request $request, $id);
    public function handleScUpdate(Request $request, $id);
    public function handleCrmUpdate(Request $request);
    public function changeStatus($id, $status);
    public function handleSUpdate(Request $request, $id);
}

class AdminUpdateController extends Controller implements AdminUpdate
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Account Information Update
    |--------------------------------------------------------------------------
    */
    public function handleAccountInformationUpdate(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:1', 'max:250'],
            'email' => ['required', 'string', 'min:1', 'max:250', Rule::unique('admins')->ignore(auth()->user()->id, 'id')],
            'phone' => ['required', 'numeric', 'digits_between:10,20', Rule::unique('admins')->ignore(auth()->user()->id, 'id')],
            'profile' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp'],
            'account_password' => ['required', 'string', 'min:1', 'max:250'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        } else {
            if (Hash::check($request->input('account_password'), auth()->user()->password)) {

                $admin = Admin::find(auth()->user()->id);
                $admin->name = $request->input('name');
                $admin->email = $request->input('email');
                $admin->phone = $request->input('phone');

                if ($request->hasFile('profile')) {
                    $profileFileName = $request->file('profile')->getClientOriginalName();
                    $destinationPath = public_path('admin/profile');
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }
                    $request->file('profile')->move($destinationPath, $profileFileName);
                    $admin->profile = $profileFileName;
                }
                $result = $admin->update();

                if ($result) {
                    return redirect()->back()->with('message', [
                        'status' => 'success',
                        'title' => 'Changes Saved',
                        'description' => 'The changes are successfully saved'
                    ]);
                } else {
                    return redirect()->back()->with('message', [
                        'status' => 'error',
                        'title' => 'An error occcured',
                        'description' => 'There is an internal server issue please try again.'
                    ]);
                }
            } else {
                return redirect()->back()->withErrors(['account_password' => 'Incorrect password']);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle CRM Settings Update
    |--------------------------------------------------------------------------
    */
    public function handleCrmUpdate(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'crm_name' => ['required', 'string', 'min:1', 'max:250'],
            'round_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'text_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:512'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        }

        $destinationPath = 'admin/crm_logo';

        $crmSettings = CrmSetting::first();

        if (!$crmSettings) {
            $crmSettings = new CrmSetting();
        }

        if (!$crmSettings) {
            return redirect()->back()->withErrors(['crm_settings' => 'CRM settings not found.']);
        }


        $crmSettings->crm_name = $request->crm_name;

        if ($request->hasFile('round_logo')) {
            $roundLogoFile = $request->file('round_logo');
            $roundLogoName = time() . '_round_' . uniqid() . '.' . $roundLogoFile->getClientOriginalExtension();

            if ($crmSettings->round_logo && file_exists(public_path($destinationPath . '/' . $crmSettings->round_logo))) {
                unlink(public_path($destinationPath . '/' . $crmSettings->round_logo));
            }

            $roundLogoFile->move(public_path($destinationPath), $roundLogoName);
            $crmSettings->round_logo = $roundLogoName;
        }

        if ($request->hasFile('text_logo')) {
            $textLogoFile = $request->file('text_logo');
            $textLogoName = time() . '_text_' . uniqid() . '.' . $textLogoFile->getClientOriginalExtension();

            if ($crmSettings->text_logo && file_exists(public_path($destinationPath . '/' . $crmSettings->text_logo))) {
                unlink(public_path($destinationPath . '/' . $crmSettings->text_logo));
            }

            $textLogoFile->move(public_path($destinationPath), $textLogoName);
            $crmSettings->text_logo = $textLogoName;
        }

        if ($request->hasFile('favicon')) {
            $faviconFile = $request->file('favicon');
            $faviconName = time() . '_favicon_' . uniqid() . '.' . $faviconFile->getClientOriginalExtension();

            if ($crmSettings->favicon && file_exists(public_path($destinationPath . '/' . $crmSettings->favicon))) {
                unlink(public_path($destinationPath . '/' . $crmSettings->favicon));
            }

            $faviconFile->move(public_path($destinationPath), $faviconName);
            $crmSettings->favicon = $faviconName;
        }

        // Save updates manually
        $result = $crmSettings->save();

        if ($result) {
            return redirect()->back()->with('message', [
                'status' => 'success',
                'title' => 'Changes Saved',
                'description' => 'The changes have been successfully saved.',
            ]);
        } else {
            return redirect()->back()->with('message', [
                'status' => 'error',
                'title' => 'Error Occurred',
                'description' => 'An internal server issue occurred. Please try again.',
            ]);
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Handle Account Password Update
    |--------------------------------------------------------------------------
    */
    public function handleAccountPasswordUpdate(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'current_password' => ['required', 'string', 'min:1', 'max:250'],
            'password' => ['required', 'string', 'min:6', 'max:20', 'confirmed'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        } else {
            if (Hash::check($request->input('current_password'), auth()->user()->password)) {

                $admin = Admin::find(auth()->user()->id);
                $admin->password = Hash::make($request->input('password'));
                $result = $admin->update();

                if ($result) {
                    return redirect()->back()->with('message', [
                        'status' => 'success',
                        'title' => 'Password Updated',
                        'description' => 'The password is successfully updated'
                    ]);
                } else {
                    return redirect()->back()->with('message', [
                        'status' => 'error',
                        'title' => 'An error occcured',
                        'description' => 'There is an internal server issue please try again.'
                    ]);
                }
            } else {
                return redirect()->back()->withErrors(['current_password' => 'Incorrect password']);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Employee Update
    |--------------------------------------------------------------------------
    */
    public function handleEmployeeUpdate(Request $request, $id)
    {
        // dd($request->all());
        $validation = Validator::make($request->all(), [
            'firstname' => ['required', 'string', 'min:1', 'max:250'],
            'lastname' => ['required', 'string', 'min:1', 'max:250'],
            'email' => ['required', 'string', 'min:1', 'max:250', Rule::unique('employees')->ignore($id, 'id')],
            'email_official' => ['nullable', 'string', 'min:1', 'max:250'],
            'phone' => ['required', 'numeric', 'digits_between:10,20', Rule::unique('employees')->ignore($id, 'id')],
            'phone_alternate' => ['nullable', 'numeric', 'digits_between:10,20'],
            'role' => ['required', 'string'],
            'home' => ['required', 'string'],
            'street' => ['required', 'string'],
            'city' => ['required', 'string'],
            'pincode' => ['required', 'string'],
            'state' => ['required', 'string'],
            'country' => ['required', 'string'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        } else {


            $employee = Employee::where('id', $id)->first();
            $employee->firstname = $request->input('firstname');
            $employee->lastname = $request->input('lastname');
            $employee->name = ucfirst($request->firstname) . ' ' . ucfirst($request->lastname);
            $employee->email = $request->input('email');
            $employee->email_official = $request->input('email_official');
            $employee->phone = $request->input('phone');
            $employee->phone_alternate = $request->input('phone_alternate');
            $employee->role = $request->input('role');
            $employee->home = $request->input('home');
            $employee->street = $request->input('street');
            $employee->city = $request->input('city');
            $employee->pincode = $request->input('pincode');
            $employee->state = $request->input('state');
            $employee->country = $request->input('country');
            if ($request->input('password')) {
                $employee->password = Hash::make($request->input('password'));
            }

            $result = $employee->save();

            if ($result) {
                return redirect()->back()->with('message', [
                    'status' => 'success',
                    'title' => 'Changes Saved',
                    'description' => 'The changes are successfully saved'
                ]);
            } else {
                return redirect()->back()->with('message', [
                    'status' => 'error',
                    'title' => 'An error occcured',
                    'description' => 'There is an internal server issue please try again.'
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Group Update
    |--------------------------------------------------------------------------
    */
    public function handleGroupUpdate(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'name' => ['required'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        } else {
            $group = Group::where('id', $id)->first();
            $group->name = $request->input('name');
            $result = $group->save();

            if ($result) {
                return redirect()->back()->with('message', [
                    'status' => 'success',
                    'title' => 'Changes Saved',
                    'description' => 'The changes are successfully saved'
                ]);
            } else {
                return redirect()->back()->with('message', [
                    'status' => 'error',
                    'title' => 'An error occcured',
                    'description' => 'There is an internal server issue please try again.'
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Leads Manager Update
    |--------------------------------------------------------------------------
    */
    public function handleLeadsManagerUpdate(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'name' => ['required'],
            'phone' => ['required'],

        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        } else {
            $leads_manager = LeadsManager::where('id', $id)->first();
            $leads_manager->name = $request->input('name');
            $leads_manager->email = $request->input('email');
            $leads_manager->phone = $request->input('phone');
            $leads_manager->address = $request->input('address');
            $leads_manager->status = $request->input('status');
            $leads_manager->remarks = $request->input('remarks');
            $result = $leads_manager->save();

            // Handle the new remark
            if ($request->filled('new_remark')) {
                // This is the beautiful, clean way to do it!
                $leads_manager->remarks()->create([
                    'comment' => $request->input('new_remark')
                ]);
            }

            if ($result) {
                return redirect()->route('admin.view.lead.manager.list')->with('message', [
                    'status' => 'success',
                    'title' => 'Changes Saved',
                    'description' => 'The changes are successfully saved'
                ]);
            } else {
                return redirect()->back()->with('message', [
                    'status' => 'error',
                    'title' => 'An error occurred',
                    'description' => 'There is an internal server issue, please try again.'
                ]);
            }

        }
    }

    // Handle Update Roles
    public function handleRoleUpdate(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            // Ensure the name is unique, but ignore the current role's name
            'name' => 'required|string|max:191|unique:roles,name,' . $role->id,
            'slug' => 'required|string|max:191|unique:roles,slug,' . $role->slug,
        ]);

        $role->update($request->only('name', 'slug'));

        return redirect()->route('admin.view.role.list')->with('success', 'Role updated successfully.');
    }


    /*
    |--------------------------------------------------------------------------
    | Handle Campaign Update
    |--------------------------------------------------------------------------
    */
    public function handleCampaignUpdate(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:1', 'max:250'],
            'lead_count' => ['required', 'numeric'],
            'group_id' => ['required'],
            'employee_id' => ['required'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        } else {

            $campaign = Campaign::find($id);
            $campaign->name = $request->input('name');
            $result = $campaign->update();

            Lead::where('campaign_id', null)->where('employee_id', null)->where('group_id', $request->group_id)->limit($request->lead_count)
                ->update([
                    'employee_id' => $request->employee_id,
                    'campaign_id' => $id,
                ]);

            if ($result) {
                return redirect()->back()->with('message', [
                    'status' => 'success',
                    'title' => 'Changes Saved',
                    'description' => 'The changes are successfully saved'
                ]);
            } else {
                return redirect()->back()->with('message', [
                    'status' => 'error',
                    'title' => 'An error occurred',
                    'description' => 'There is an internal server issue please try again.'
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Customer Update
    |--------------------------------------------------------------------------
    */
    public function handleCustomerUpdate(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:1', 'max:250'],
            'email' => ['nullable', 'string', 'min:1', 'max:250', Rule::unique('customers')->ignore($id, 'id')],
            'phone' => ['nullable', 'numeric', Rule::unique('customers')->ignore($id, 'id')],
            'phone_alternate' => ['nullable', 'numeric'],
            'whatsapp' => ['nullable', 'numeric'],
            'company_name' => ['nullable', 'string'],
            'website' => ['nullable', 'string'],
            'street' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'pincode' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'country' => ['nullable', 'string'],
            'profile' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        } else {

            $customer = Customer::find($id);
            $customer->name = $request->input('name');
            $customer->email = $request->input('email');
            $customer->phone = $request->input('phone');
            $customer->phone_alternate = $request->input('phone_alternate');
            $customer->whatsapp = $request->input('whatsapp');
            $customer->company_name = $request->input('company_name');
            $customer->website = $request->input('website');
            $customer->street = $request->input('street');
            $customer->city = $request->input('city');
            $customer->pincode = $request->input('pincode');
            $customer->state = $request->input('state');
            $customer->country = $request->input('country');

            if ($request->other_name) {
                $other = [];
                for ($i = 0; $i < count($request->input('other_name')); $i++) {
                    array_push($other, [
                        'name' => $request->other_name[$i],
                        'value'  => $request->other_value[$i],
                    ]);
                }
                $customer->other = json_encode($other);
            } else {
                $customer->other = null;
            }

            if ($request->hasFile('profile')) {
                if ($customer->profile && file_exists(public_path('admin/customers/' . $customer->profile))) {
                    unlink(public_path('admin/customers/' . $customer->profile));
                }
                $file = $request->file('profile');
                $filename = time() . '-' . $file->getClientOriginalName();
                $file->move(public_path('admin/customers'), $filename);
                $customer->profile = $filename;
            }

            $result = $customer->update();


            if ($result) {
                return redirect()->back()->with('message', [
                    'status' => 'success',
                    'title' => 'Changes Saved',
                    'description' => 'The changes are successfully saved'
                ]);
            } else {
                return redirect()->back()->with('message', [
                    'status' => 'error',
                    'title' => 'An error occurred',
                    'description' => 'There is an internal server issue please try again.'
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Project Update
    |--------------------------------------------------------------------------
    */
    public function handleProjectUpdate(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:1', 'max:250'],
            'project_link' => ['nullable', 'string'],
            'resource_link' => ['nullable', 'string'],
            'start_date' => ['nullable', 'string'],
            'end_date' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'pending_amount' => ['required', 'numeric'],
            'status' => ['required', 'string'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        } else {

            $project = Project::find($id);
            $project->name = $request->input('name');
            $project->project_link = $request->input('project_link');
            $project->resource_link = $request->input('resource_link');
            $project->start_date = $request->input('start_date');
            $project->end_date = $request->input('end_date');
            $project->amount = $request->input('amount');
            $project->pending_amount = $request->input('pending_amount');
            $project->status = $request->input('status');
            $result  = $project->update();

            if ($result) {
                return redirect()->back()->with('message', [
                    'status' => 'success',
                    'title' => 'Changes Saved',
                    'description' => 'The changes are successfully saved'
                ]);
            } else {
                return redirect()->back()->with('message', [
                    'status' => 'error',
                    'title' => 'An error occurred',
                    'description' => 'There is an internal server issue please try again.'
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Payment Update
    |--------------------------------------------------------------------------
    */
    public function handlePaymentUpdate(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'type' => ['required', 'string', 'min:1', 'max:250'],
            'amount' => ['required', 'numeric'],
            'remark' => ['nullable', 'string'],
            'method' => ['required', 'string'],
            'date' => ['required', 'string']
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        } else {

            $payment = Payment::find($id);
            $payment->type = $request->input('type');
            $payment->amount = $request->input('amount');
            $payment->remark = $request->input('remark');
            $payment->method = $request->input('method');
            $payment->date = $request->input('date');
            $result  = $payment->update();

            if ($result) {
                return redirect()->back()->with('message', [
                    'status' => 'success',
                    'title' => 'Changes Saved',
                    'description' => 'The changes are successfully saved'
                ]);
            } else {
                return redirect()->back()->with('message', [
                    'status' => 'error',
                    'title' => 'An error occurred',
                    'description' => 'There is an internal server issue please try again.'
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Company Details Update
    |--------------------------------------------------------------------------
    */

    public function handleCompanyDetailsUpdate(Request $request)
    {
        // Validate the request data
        $validation = Validator::make($request->all(), [
            'company_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,webp,png'],
            'brand_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,webp,png'],
            'company_name' => ['nullable', 'string'],
            'brand_name' => ['nullable', 'string'],
            'company_email' => ['nullable', 'string'],
            'company_phone' => ['nullable', 'numeric'],
            'company_phone_alternate' => ['nullable', 'numeric'],
            'company_website' => ['nullable', 'string'],
            'company_account_type' => ['nullable', 'string'],
            'company_account_no' => ['nullable', 'string'],
            'company_account_holder' => ['nullable', 'string'],
            'company_account_ifsc' => ['nullable', 'string'],
            'company_account_branch' => ['nullable', 'string'],
            'company_account_vpa' => ['nullable', 'string'],
            'billing_tax_percentage' => ['nullable', 'string'],
            'company_address_street' => ['nullable', 'string'],
            'company_address_city' => ['nullable', 'string'],
            'company_address_pincode' => ['nullable', 'string'],
            'company_address_state' => ['nullable', 'string'],
            'company_address_country' => ['nullable', 'string'],
            'company_social_media_facebook' => ['nullable', 'string'],
            'company_social_media_twitter' => ['nullable', 'string'],
            'company_social_media_instagram' => ['nullable', 'string'],
            'company_social_media_linkedin' => ['nullable', 'string'],
            'company_social_media_youtube' => ['nullable', 'string'],
            // 'company_gst_number' => ['nullable', 'string'],
            'company_gst_number' => ['nullable', 'string', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z1-9]{1}[Z]{1}[0-9A-Z]{1}$/'],

        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        }
        // Get the current company details to update
        $companyDetails = CompanyDetail::first();

        if (!$companyDetails) {
            $companyDetails = new CompanyDetail();
        }
        // Handle company logo upload
        if ($request->hasFile('company_logo')) {
            $logoFileName = $request->file('company_logo')->getClientOriginalName();
            $destinationPath = public_path('admin/company_logo');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $request->file('company_logo')->move($destinationPath, $logoFileName);
            $companyDetails->company_logo =  $logoFileName;
        }

        // Handle brand logo upload
        if ($request->hasFile('brand_logo')) {
            $logoFileName = $request->file('brand_logo')->getClientOriginalName();
            $destinationPath = public_path('admin/brand_logo');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $request->file('brand_logo')->move($destinationPath, $logoFileName);
            $companyDetails->brand_logo =  $logoFileName;
        }

        // Update other fields
        $companyDetails->brand_name = $request->input('brand_name', $companyDetails->brand_name);
        $companyDetails->company_name = $request->input('company_name', $companyDetails->company_name);
        $companyDetails->company_email = $request->input('company_email', $companyDetails->company_email);
        $companyDetails->company_phone = $request->input('company_phone', $companyDetails->company_phone);
        $companyDetails->company_phone_alternate = $request->input('company_phone_alternate', $companyDetails->company_phone_alternate);
        $companyDetails->company_website = $request->input('company_website', $companyDetails->company_website);
        $companyDetails->company_account_type = $request->input('company_account_type', $companyDetails->company_account_type);
        $companyDetails->company_account_no = $request->input('company_account_no', $companyDetails->company_account_no);
        $companyDetails->company_account_holder = $request->input('company_account_holder', $companyDetails->company_account_holder);
        $companyDetails->company_account_ifsc = $request->input('company_account_ifsc', $companyDetails->company_account_ifsc);
        $companyDetails->company_account_branch = $request->input('company_account_branch', $companyDetails->company_account_branch);
        $companyDetails->company_account_vpa = $request->input('company_account_vpa', $companyDetails->company_account_vpa);
        $companyDetails->billing_tax_percentage = $request->input('billing_tax_percentage', $companyDetails->billing_tax_percentage);
        $companyDetails->company_address_street = $request->input('company_address_street', $companyDetails->company_address_street);
        $companyDetails->company_address_city = $request->input('company_address_city', $companyDetails->company_address_city);
        $companyDetails->company_address_pincode = $request->input('company_address_pincode', $companyDetails->company_address_pincode);
        $companyDetails->company_address_state = $request->input('company_address_state', $companyDetails->company_address_state);
        $companyDetails->company_address_country = $request->input('company_address_country', $companyDetails->company_address_country);
        $companyDetails->company_social_media_facebook = $request->input('company_social_media_facebook', $companyDetails->company_social_media_facebook);
        $companyDetails->company_social_media_twitter = $request->input('company_social_media_twitter', $companyDetails->company_social_media_twitter);
        $companyDetails->company_social_media_instagram = $request->input('company_social_media_instagram', $companyDetails->company_social_media_instagram);
        $companyDetails->company_social_media_linkedin = $request->input('company_social_media_linkedin', $companyDetails->company_social_media_linkedin);
        $companyDetails->company_social_media_youtube = $request->input('company_social_media_youtube', $companyDetails->company_social_media_youtube);
        $companyDetails->company_gst_number = $request->input('company_gst_number', $companyDetails->company_gst_number);

        // Save the company details
        $companyDetails->save();

        return redirect()->back()->with('message', [
            'status' => 'success',
            'title' => 'Changes Saved',
            'description' => 'The changes are successfully saved'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Handle payment setting Update
    |--------------------------------------------------------------------------
    */
    public function handlePaymentSettingUpdate(Request $request)
    {
        $validation = Validator::make($request->all(), [
            // INR payment fields
            'account_type_inr' => 'nullable|string',
            'company_account_number_inr' => 'nullable|string|min:10|max:18',
            'company_account_holder_inr' => 'nullable|string|max:255',
            'company_account_ifsc_inr' => 'nullable|string|regex:/^[A-Za-z]{4}[0][A-Za-z0-9]{6}$/',
            'company_account_branch_inr' => 'nullable|string|max:255',
            'upi_payment_inr' => 'nullable|string|regex:/^[a-zA-Z0-9_.-]+@[a-zA-Z0-9.-]+$/',
            'payment_link_inr' => 'nullable|url',

            // USD payment fields
            'company_account_holder_usd' => 'nullable|string|max:255',
            'payment_method_usd' => 'nullable|string|max:255',
            'ach_routing_number_usd' => 'nullable|string|regex:/^\d{9}$/',
            'company_account_number_usd' => 'nullable|string|min:10|max:18',
            'bank_name_usd' => 'nullable|string|max:255',
            'beneficiary_address_usd' => 'nullable|string|max:255',

            // AUD payment fields
            'account_holder_aud' => 'nullable|string|max:255',
            'payment_method_aud' => 'nullable|string|max:255',
            'company_account_number_aud' => 'nullable|string|min:10|max:18',
            'bsb_number_aud' => 'nullable|string|regex:/^\d{6}$/',
            'bank_name_aud' => 'nullable|string|max:255',
            'beneficiary_address_aud' => 'nullable|string|max:255',
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        }

        // Process INR Payment Settings
        if ($request->has('company_account_number_inr') || $request->has('upi_payment_inr') || $request->has('payment_link_inr')) {
            // Check if INR payment setting already exists, otherwise create new record
            $paymentSettingINR = PaymentSetting::first();

            if (!$paymentSettingINR) {
                $paymentSettingINR = new PaymentSetting();
                // $paymentSettingINR->currency = 'INR';
            }

            // Update INR payment details
            $paymentSettingINR->account_type_inr = $request->input('account_type_inr');
            $paymentSettingINR->company_account_number_inr = $request->input('company_account_number_inr');
            $paymentSettingINR->company_account_holder_inr = $request->input('company_account_holder_inr');
            $paymentSettingINR->company_account_ifsc_inr = $request->input('company_account_ifsc_inr');
            $paymentSettingINR->company_account_branch_inr = $request->input('company_account_branch_inr');
            $paymentSettingINR->upi_payment_inr = $request->input('upi_payment_inr');
            $paymentSettingINR->payment_link_inr = $request->input('payment_link_inr');

            // Save the INR payment settings
            $paymentSettingINR->save();
        }

        // Process USD Payment Settings
        if ($request->has('company_account_number_usd') || $request->has('ach_routing_number_usd') || $request->has('payment_method_usd')) {
            // Check if USD payment setting already exists, otherwise create new record
            $paymentSettingUSD = PaymentSetting::first();

            if (!$paymentSettingUSD) {
                $paymentSettingUSD = new PaymentSetting();
            }

            // Update USD payment details
            $paymentSettingUSD->company_account_holder_usd = $request->input('company_account_holder_usd');
            $paymentSettingUSD->payment_method_usd = $request->input('payment_method_usd');
            $paymentSettingUSD->ach_routing_number_usd = $request->input('ach_routing_number_usd');
            $paymentSettingUSD->company_account_number_usd = $request->input('company_account_number_usd');
            $paymentSettingUSD->bank_name_usd = $request->input('bank_name_usd');
            $paymentSettingUSD->beneficiary_address_usd = $request->input('beneficiary_address_usd');

            // Save the USD payment settings
            $paymentSettingUSD->save();
        }

        // Process AUD Payment Settings
        if ($request->has('company_account_number_aud') || $request->has('bsb_number_aud') || $request->has('payment_method_aud')) {
            $paymentSettingAUD = PaymentSetting::first();

            if (!$paymentSettingAUD) {
                $paymentSettingAUD = new PaymentSetting();
            }

            // Update AUD payment details
            $paymentSettingAUD->account_holder_aud = $request->input('account_holder_aud');
            $paymentSettingAUD->payment_method_aud = $request->input('payment_method_aud');
            $paymentSettingAUD->company_account_number_aud = $request->input('company_account_number_aud');
            $paymentSettingAUD->bsb_number_aud = $request->input('bsb_number_aud');
            $paymentSettingAUD->bank_name_aud = $request->input('bank_name_aud');
            $paymentSettingAUD->beneficiary_address_aud = $request->input('beneficiary_address_aud');

            // Save the AUD payment settings
            $paymentSettingAUD->save();
        }

        return redirect()->back()->with('message', [
            'status' => 'success',
            'title' => 'Payment Settings Updated',
            'description' => 'The payment settings have been successfully updated.'
        ]);
    }






    /*
    |--------------------------------------------------------------------------
    | Handle Mail Credentials Update
    |--------------------------------------------------------------------------
    */
    public function handleMailCredentialsUpdate(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'mail_host' => ['nullable', 'string'],
            'mail_port' => ['nullable', 'string'],
            'mail_username' => ['nullable', 'string'],
            'mail_password' => ['nullable', 'string'],
            'mail_encryption' => ['nullable', 'string'],
            'mail_address' => ['nullable', 'string'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        } else {

            MailCredential::where('name', 'mail_host')->update(['value' => $request->input('mail_host')]);
            MailCredential::where('name', 'mail_port')->update(['value' => $request->input('mail_port')]);
            MailCredential::where('name', 'mail_username')->update(['value' => $request->input('mail_username')]);
            MailCredential::where('name', 'mail_password')->update(['value' => $request->input('mail_password')]);
            MailCredential::where('name', 'mail_encryption')->update(['value' => $request->input('mail_encryption')]);
            MailCredential::where('name', 'mail_address')->update(['value' => $request->input('mail_address')]);

            return redirect()->back()->with('message', [
                'status' => 'success',
                'title' => 'Changes Saved',
                'description' => 'The changes are successfully saved'
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Bill Update
    |--------------------------------------------------------------------------
    */
    public function handleBillUpdate(Request $request, $id)
    {

        $validation = Validator::make($request->all(), [
            'customer_id' => ['required'],
            'total' => ['required', 'numeric'],
            'bill_date' => ['required', 'string'],
            'due_date' => ['required', 'string'],
            'invoice_currency' => ['required', 'string'],
            'bill_note' => ['nullable', 'string'],
            'payment_status' => ['nullable', 'string'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        } else {

            if (!$request->input('bill_item_name')) {
                return redirect()->back()->with('message', [
                    'status' => 'warning',
                    'title' => 'Please Add Items',
                    'description' => 'You did not added any items in the bill.'
                ]);
            }

            $items = [];

            for ($i = 0; $i < count($request->input('bill_item_name')); $i++) {
                array_push($items, [
                    'bill_item_name' => $request->bill_item_name[$i],
                    'bill_item_quantity'  => $request->bill_item_quantity[$i],
                    'bill_item_price'  => $request->bill_item_price[$i],
                    'bill_item_total'  => $request->bill_item_total[$i],
                ]);
            }

            $bill = Bill::find($id);
            $bill->customer_id = $request->input('customer_id');
            $bill->items = json_encode($items);
            if ($request->apply_gst) {
                $bill->tax = $request->input('tax');
            } else {
                $bill->tax = null;
            }
            $bill->payment_status = $request->input('payment_status');
            $bill->total = $request->input('total');
            $bill->bill_date = $request->input('bill_date');
            $bill->due_date = $request->input('due_date');
            $bill->invoice_currency = $request->input('invoice_currency');
            $bill->bill_note = $request->input('bill_note');
            $result = $bill->update();

            if ($result) {
                return redirect()->back()->with('message', [
                    'status' => 'success',
                    'title' => 'Changes Saved',
                    'description' => 'The changes are successfully saved'
                ]);
            } else {
                return redirect()->back()->with('message', [
                    'status' => 'error',
                    'title' => 'An error occurred',
                    'description' => 'There is an internal server issue please try again.'
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Admin Update
    |--------------------------------------------------------------------------
    */
    public function handleAdminUpdate(Request $request, $id)
    {

        $validation = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:1', 'max:250'],
            'email' => ['required', 'string', 'min:1', 'max:250', Rule::unique('admins')->ignore($id, 'id')],
            'phone' => ['required', 'numeric', Rule::unique('admins')->ignore($id, 'id')],
            'role' => ['required', 'string', 'min:1', 'max:250'],
            'profile' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        } else {

            if ($request->input('password_change')) {
                if ($request->input('password') != $request->input('password_confirmation')) {
                    return redirect()->back()->withErrors(['password' => 'The password confirmation does not match.'])->withInput();
                }
            }

            $admin = Admin::find($id);
            $admin->name = $request->input('name');
            $admin->email = $request->input('email');
            $admin->phone = $request->input('phone');
            $admin->role = $request->input('role');
            if ($request->input('password')) {
                $admin->password = Hash::make($request->input('password'));
            }

            if ($request->hasFile('profile')) {
                $profileFileName = $request->file('profile')->getClientOriginalName();
                $destinationPath = public_path('admin/profile');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $request->file('profile')->move($destinationPath, $profileFileName);

                $admin->profile = $profileFileName;
            }

            $result = $admin->update();

            if ($result) {
                return redirect()->back()->with('message', [
                    'status' => 'success',
                    'title' => 'Changes Saved',
                    'description' => 'The changes are successfully saved'
                ]);
            } else {
                return redirect()->back()->with('message', [
                    'status' => 'error',
                    'title' => 'An error occcured',
                    'description' => 'There is an internal server issue please try again.'
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Domain Hosting Update
    |--------------------------------------------------------------------------
    */
    public function handleDomainHostingUpdate(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'customer_id' => ['nullable', 'string', 'min:1', 'max:250'],

            'domain_name' => ['nullable', 'string', 'min:1', 'max:250'],
            'domain_purchase' => ['nullable'],
            // 'domain_expiry' => ['nullable'],
            'domain_provider' => ['nullable', 'string', 'min:1', 'max:250'],
            'domain_renewal_price' => ['nullable', 'numeric'],

            'hosting_purchase' => ['nullable'],
            // 'hosting_expiry' => ['nullable'],
            'hosting_provider' => ['nullable', 'string', 'min:1', 'max:250'],
            'hosting_renewal_price' => ['nullable', 'numeric'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        } else {

            $domain_hosting = DomainHosting::find($id);
            $domain_hosting->customer_id = $request->input('customer_id');
            $domain_hosting->domain_name = $request->input('domain_name');
            $domain_hosting->domain_purchase = $request->input('domain_purchase');
            // $domain_hosting->domain_expiry = $request->input('domain_expiry');
            $domain_hosting->domain_provider = $request->input('domain_provider');
            $domain_hosting->domain_renewal_price = $request->input('domain_renewal_price');
            $domain_hosting->hosting_purchase = $request->input('hosting_purchase');
            // $domain_hosting->hosting_expiry = $request->input('hosting_expiry');
            $domain_hosting->hosting_provider = $request->input('hosting_provider');
            $domain_hosting->hosting_renewal_price = $request->input('hosting_renewal_price');
            $result = $domain_hosting->update();

            if ($result) {
                return redirect()->back()->with('message', [
                    'status' => 'success',
                    'title' => 'Changes Saved',
                    'description' => 'The changes are successfully saved'
                ]);
            } else {
                return redirect()->back()->with('message', [
                    'status' => 'error',
                    'title' => 'An error occcured',
                    'description' => 'There is an internal server issue please try again.'
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Password Update
    |--------------------------------------------------------------------------
    */
    public function handlePasswordUpdate(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'customer_id' => ['nullable', 'string', 'min:1', 'max:250'],
            'username' => ['nullable', 'string', 'min:1', 'max:250'],
            'email' => ['nullable', 'string', 'min:1', 'max:250'],
            'phone' => ['nullable', 'string', 'min:1', 'max:250'],
            'password' => ['nullable', 'string', 'min:1', 'max:250'],
            'type' => ['required', 'string', 'min:1', 'max:250'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        } else {

            $password = Password::find($id);
            $password->customer_id = $request->input('customer_id');
            $password->username = $request->input('username');
            $password->email = $request->input('email');
            $password->phone = $request->input('phone');
            $password->password = $request->input('password');
            $password->type = $request->input('type');
            $result = $password->update();

            if ($result) {
                return redirect()->back()->with('message', [
                    'status' => 'success',
                    'title' => 'Changes Saved',
                    'description' => 'The changes are successfully saved'
                ]);
            } else {
                return redirect()->back()->with('message', [
                    'status' => 'error',
                    'title' => 'An error occcured',
                    'description' => 'There is an internal server issue please try again.'
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Plan Update
    |--------------------------------------------------------------------------
    */
    public function handlePlanUpdate(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:1', 'max:250'],
            'city' => ['required', 'string', 'min:1', 'max:250'],
            'summary' => ['nullable', 'string', 'min:1', 'max:500'],
            'duration' => ['required', 'numeric'],
            'price_regular' => ['required', 'numeric'],
            'price_offer' => ['nullable', 'numeric'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        } else {

            $plan = Plan::find($id);
            $plan->name = $request->input('name');
            $plan->city = $request->input('city');
            $plan->summary = $request->input('summary');
            $plan->duration = $request->input('duration');
            $plan->price_regular = $request->input('price_regular');
            $plan->price_offer = $request->input('price_offer');
            $result = $plan->update();

            if ($result) {
                return redirect()->back()->with('message', [
                    'status' => 'success',
                    'title' => 'Changes Saved',
                    'description' => 'The changes are successfully saved'
                ]);
            } else {
                return redirect()->back()->with('message', [
                    'status' => 'error',
                    'title' => 'An error occcured',
                    'description' => 'There is an internal server issue please try again.'
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Package Update
    |--------------------------------------------------------------------------
    */
    public function handlePackageUpdate(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'customer_id' => ['required'],
            'plan_id' => ['required'],
            'start_date' => ['required', 'string', 'min:1', 'max:250'],
            'end_date' => ['required', 'string', 'min:1', 'max:250'],
            'payment_status' => ['required', 'string', 'min:1', 'max:250'],
            'status' => ['required', 'string', 'min:1', 'max:250'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        } else {

            $package = Package::find($id);
            $package->customer_id = $request->input('customer_id');
            $package->plan_id = $request->input('plan_id');
            $package->start_date = $request->input('start_date');
            $package->end_date = $request->input('end_date');
            $package->payment_status = $request->input('payment_status');
            $package->status = $request->input('status');
            $result = $package->update();

            if ($result) {
                return redirect()->back()->with('message', [
                    'status' => 'success',
                    'title' => 'Changes Saved',
                    'description' => 'The changes are successfully saved'
                ]);
            } else {
                return redirect()->back()->with('message', [
                    'status' => 'error',
                    'title' => 'An error occcured',
                    'description' => 'There is an internal server issue please try again.'
                ]);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Handle Package Renew
    |--------------------------------------------------------------------------
    */
    public function handlePackageRenew(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'renew_date' => ['required', 'string', 'min:1', 'max:250'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        } else {
            $package = Package::find($id);
            $plan = Plan::find($package->plan_id);
            $package->start_date = $request->input('renew_date');
            $package->end_date = Carbon::create($request->input(('renew_date')))->addDays($plan->duration);
            $result = $package->update();

            if ($result) {
                return redirect()->route('admin.view.package.list')->with('message', [
                    'status' => 'success',
                    'title' => 'Package Renewed',
                    'description' => 'The package is successfully renewed'
                ]);
            } else {
                return redirect()->back()->with('message', [
                    'status' => 'error',
                    'title' => 'An error occcured',
                    'description' => 'There is an internal server issue please try again.'
                ]);
            }
        }
    }



    public function handleScUpdate(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'name' => ['required'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        } else {
            $service_cat = ServiceCategory::where('id', $id)->first();
            $service_cat->name = $request->input('name');
            $result = $service_cat->save();

            if ($result) {
                return redirect()->back()->with('message', [
                    'status' => 'success',
                    'title' => 'Changes Saved',
                    'description' => 'The changes are successfully saved'
                ]);
            } else {
                return redirect()->back()->with('message', [
                    'status' => 'error',
                    'title' => 'An error occcured',
                    'description' => 'There is an internal server issue please try again.'
                ]);
            }
        }
    }

    public function changeStatus($id, $status)
    {
        // dd("test");
        $validStatuses = ['OnProgress', 'Pending', 'Closed'];
        if (!in_array($status, $validStatuses)) {
            return redirect()->back()->with('error', 'Invalid status');
        }
        $project = Project::findOrFail($id);
        if ($project->status === 'Pending' && $status === 'OnProgress') {
            $project->status = 'OnProgress';
        } elseif ($project->status === 'OnProgress' && $status === 'Closed') {
            $project->status = 'Closed';
        }

        $result = $project->save();


        if ($result) {
            return redirect()->back()->with('message', [
                'status' => 'success',
                'title' => 'Changes Saved',
                'description' => 'The status are successfully saved'
            ]);
        } else {
            return redirect()->back()->with('message', [
                'status' => 'error',
                'title' => 'An error occcured',
                'description' => 'There is an internal server issue please try again.'
            ]);
        }
    }


    public function handleSUpdate(Request $request, $id)
    {
        // Validate the request
        $validated = $request->validate([
            'service_name' => 'required|string|max:255',
            'service_category_id' => 'required|exists:service_categories,id',
            'service_price_in_inr' => 'required|numeric',
            'service_price_in_usd' => 'nullable|numeric',
            'service_price_in_aud' => 'nullable|numeric',
            'discounted_price' => 'nullable|numeric',
            'govt_fee' => 'nullable|numeric',
            'subscription_duration' => 'nullable|in:0,30,90,180,365',
            'partner_margin_percentage' => 'nullable|numeric',
            'service_details' => 'nullable|string',

            // Service documents
            'documents' => 'nullable|array',
            'documents.*.name' => 'nullable|string',
            'documents.*.file' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            'documents.*.is_required' => 'nullable',
        ]);

        // Retrieve the service by ID
        $service = Service::find($id);

        // Update the service data
        $service->service_name = $request->input('service_name');
        $service->service_category_id = $request->input('service_category_id');
        $service->service_price_in_inr = $request->input('service_price_in_inr');
        $service->service_price_in_usd = $request->input('service_price_in_usd');
        $service->service_price_in_aud = $request->input('service_price_in_aud');
        $service->discounted_price = $request->input('discounted_price');
        $service->govt_fee = $request->input('govt_fee');
        $service->subscription_duration = $request->input('subscription_duration');
        $service->partner_margin_percentage = $request->input('partner_margin_percentage');
        $service->service_details = $request->input('service_details');

        // Save related documents (if any)
        if ($request->has('documents')) {
            foreach ($request->documents as $doc) {
                // Only proceed if the file is uploaded OR the name is provided
                if ((empty($doc['name']) && !isset($doc['file'])) || (empty($doc['file']) && empty($doc['name']))) {
                    continue; // Skip processing if no file and no name is provided
                }

                $path = null;

                // If the file is uploaded, process it
                if (isset($doc['file']) && $doc['file'] instanceof \Illuminate\Http\UploadedFile) {
                    // Create the destination path
                    $destinationPath = public_path('admin_new/service_document');

                    // Ensure the folder exists
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }

                    // Generate unique file name
                    $fileName = time() . '_' . uniqid() . '.' . $doc['file']->getClientOriginalExtension();

                    // Move the file to the public folder
                    $doc['file']->move($destinationPath, $fileName);

                    // Save the relative path to DB
                    $path = $fileName;
                }

                // Check if the document already exists for the given service by name
                $existingDocument = $service->documents()->where('document_name', $doc['name'])->first();

                if ($existingDocument) {
                    // If the document exists, update the existing one
                    $existingDocument->document_file = $path ?: $existingDocument->document_file; // Update file only if there's a new file
                    $existingDocument->is_required = isset($doc['is_required']) ? true : false;
                    $existingDocument->save();
                } else {
                    // If the document doesn't exist, create a new one
                    if (!empty($doc['name']) || $path) {
                        $service->documents()->create([
                            'document_name' => $doc['name'],
                            'document_file' => $path,
                            'is_required' => isset($doc['is_required']) ? true : false,
                        ]);
                    }
                }
            }
        }

        // Save the service
        $result = $service->save();

        if ($result) {
            return redirect()->route('admin.view.service.list')->with('message', [
                'status' => 'success',
                'title' => 'Service Updated',
                'description' => 'The service has been successfully updated.'
            ]);
        } else {
            return redirect()->back()->with('message', [
                'status' => 'error',
                'title' => 'An error occurred',
                'description' => 'There was an internal server issue. Please try again.'
            ]);
        }
    }
}
