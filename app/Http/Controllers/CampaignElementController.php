<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignActions;
use App\Models\CampaignElement;
use App\Models\CampaignPath;
use App\Models\ElementProperties;
use App\Models\EmailSetting;
use App\Models\GlobalSetting;
use App\Models\ImportedLeads;
<<<<<<< HEAD
use App\Models\LeadActions;
=======
>>>>>>> seat_work
use App\Models\Leads;
use App\Models\LinkedinSetting;
use App\Models\UpdatedCampaignElements;
use App\Models\UpdatedCampaignProperties;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
<<<<<<< HEAD
use Illuminate\Support\Facades\DB;
=======
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;
>>>>>>> seat_work

class CampaignElementController extends Controller
{
    function campaignElement($slug)
    {
        if (Auth::check()) {
            $elements = CampaignElement::where('element_slug', $slug)->first();
            if ($elements) {
                $properties = ElementProperties::where('element_id', $elements->id)->get();
                if ($properties->isNotEmpty()) {
                    return response()->json(['success' => true, 'properties' => $properties]);
                } else {
                    return response()->json(['success' => false, 'message' => 'No Properties Found']);
                }
            }
        } else {
            return redirect(url('/'));
        }
    }

<<<<<<< HEAD
    public function createCampaign(Request $request)
    {
        DB::beginTransaction();
        $campaign = null;
        try {
            $user_id = Auth::user()->id;
            $seat_id = session('seat_id');
            $data = $request->all();
            $final_array = $data['final_array'];
            $final_data = $data['final_data'];
            $settings = $data['settings'];
            $img_path = $data['img_url'];
            $campaign = new Campaign([
                'campaign_name' => $settings['campaign_name'],
                'campaign_type' => $settings['campaign_type'],
                'campaign_url' => $settings['campaign_url'],
                'campaign_connection' => ($settings['campaign_type'] != 'import' && $settings['campaign_type'] != 'recruiter') ? $settings['connections'] : 'o',
                'user_id' => $user_id,
                'seat_id' => $seat_id,
                'modified_date' => now()->format('Y-m-d'),
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
                'img_path' => $img_path
            ]);
            $campaign->save();
            if (!empty($settings['campaign_url_hidden'])) {
                $imported_lead = ImportedLeads::where('user_id', $user_id)
                    ->where('file_path', $settings['campaign_url_hidden'])
                    ->first();
                if (!empty($imported_lead)) {
                    $imported_lead->update(['campaign_id' => $campaign->id]);
                    $campaign['campaign_url'] = $imported_lead['file_path'];
                    $campaign->save();
                }
            }
            $this->saveSettings($settings, $campaign->id, $user_id);
            $this->saveCampaignElements($final_array, $final_data, $campaign->id, $user_id);
            $this->createInitialCampaignAction($campaign->id);
            DB::commit();
            $request->session()->flash('success', 'Campaign successfully saved!');
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            if ($campaign !== null) {
                $this->deleteCampaignData($campaign->id);
            }
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function saveSettings($settings, $campaign_id, $user_id)
    {
        foreach ($settings as $key => $value) {
            $setting = $this->getSettingModel($key);
            if ($setting) {
                $setting->create([
                    'campaign_id' => $campaign_id,
                    'setting_slug' => $key,
                    'user_id' => $user_id,
                    'seat_id' => 1,
                    'value' => $value,
                    'setting_name' => ucwords(str_replace('_', ' ', $key)),
                ]);
            }
        }
    }

    private function getSettingModel($key)
    {
        if (str_contains($key, 'email_settings_')) {
            return new EmailSetting();
        }
        if (str_contains($key, 'linkedin_settings_')) {
            return new LinkedinSetting();
        }
        if (str_contains($key, 'global_settings_')) {
            return new GlobalSetting();
        }
        return null;
    }

    private function saveCampaignElements($final_array, $final_data, $campaign_id, $user_id)
    {
        $time = now();
        $path_array = [];
        foreach ($final_array as $key => $value) {
            if ($key != 'step' && $key != 'step-1') {
                $element = CampaignElement::where('element_slug', $this->remove_prefix($key))->first();
                if ($element) {
                    $element_item = UpdatedCampaignElements::create([
                        'element_id' => $element->id,
                        'campaign_id' => $campaign_id,
                        'user_id' => $user_id,
                        'seat_id' => 1,
                        'position_x' => $value['position_x'],
                        'position_y' => $value['position_y'],
                        'element_slug' => $key,
                    ]);
                    $path_array[$key] = $element_item->id;
                    if (isset($final_data[$key])) {
                        $this->saveElementProperties($element_item->id, $final_data[$key], $campaign_id, $time);
                    }
                }
            }
        }
        Campaign::where('id', $campaign_id)->update(['end_date' => $time]);
        foreach ($final_array as $key => $value) {
            if (isset($path_array[$key])) {
                CampaignPath::create([
                    'campaign_id' => $campaign_id,
                    'current_element_id' => $path_array[$key],
                    'next_false_element_id' => $final_array[$key]['0'] ? $path_array[$value['0']] : '',
                    'next_true_element_id' => $final_array[$key]['1'] ? $path_array[$value['1']] : '',
                ]);
            }
=======
    function createCampaign(Request $request)
    {
        if (Auth::check()) {
            $user_id = Auth::user()->id;
            $all_request = $request->all();
            $final_array = $all_request['final_array'];
            $final_data = $all_request['final_data'];
            $settings = $all_request['settings'];
            $img_path = $all_request['img_url'];
            $campaign = new Campaign();
            $campaign->campaign_name = $settings['campaign_name'];
            unset($settings['campaign_name']);
            $campaign->campaign_type = $settings['campaign_type'];
            unset($settings['campaign_type']);
            $campaign->campaign_url = $settings['campaign_url'];
            unset($settings['campaign_url']);
            $lead_details = array();
            if ($campaign->campaign_type == 'import' || $campaign->campaign_type == 'recruiter') {
                $campaign->campaign_connection = '0';
            } elseif ($campaign->campaign_type == 'sales_navigator') {
                $lead_details = $settings['lead_details'];
                $lead_details = json_decode($lead_details);
                $filters = $lead_details->query->filters;
                foreach ($filters as $filter) {
                    $type = $filter->type;
                    if ($type == "RELATIONSHIP") {
                        $values = $filter->values;
                        foreach ($values as $value) {
                            if (strpos($value->text, "1st") !== false) {
                                $campaign->campaign_connection = '1';
                            } elseif (strpos($value->text, "2nd") !== false) {
                                $campaign->campaign_connection = '2';
                            } elseif (strpos($value->text, "3rd") !== false) {
                                $campaign->campaign_connection = '3';
                            } else {
                                $campaign->campaign_connection = 'o';
                            }
                        }
                    }
                }
                unset($settings['lead_details']);
            } else {
                $campaign->campaign_connection = $settings['connections'];
                unset($settings['connections']);
            }
            $campaign->user_id = $user_id;
            $campaign->seat_id = 1;
            $campaign->description = '';
            $campaign->modified_date = date('Y-m-d');
            $campaign->start_date = date('Y-m-d');
            $campaign->end_date = date('Y-m-d');
            $campaign->img_path = $img_path;
            $campaign->save();
            $account_id = auth()->user()->account_id;
            if ($campaign->id) {
                if ($account_id != NULL) {
                    foreach ($settings as $key => $value) {
                        if (str_contains($key, 'email_settings_')) {
                            $setting = new EmailSetting();
                        }
                        if (str_contains($key, 'linkedin_settings_')) {
                            $setting = new LinkedinSetting();
                        }
                        if (str_contains($key, 'global_settings_')) {
                            $setting = new GlobalSetting();
                        }
                        $setting->campaign_id = $campaign->id;
                        $setting->setting_slug = $key;
                        $setting->user_id = $user_id;
                        $setting->seat_id = 1;
                        $setting->value = $value;
                        $setting->setting_name = ucwords(str_replace('_', ' ', $key));
                        $setting->save();
                    }
                    $path_array = [];
                    foreach ($final_array as $key => $value) {
                        if (strpos($key, 'step') === false) {
                            $element = CampaignElement::where('element_slug', $this->remove_prefix($key))->first();
                            if ($element) {
                                $element_item = new UpdatedCampaignElements();
                                $element_item->element_id = $element->id;
                                $element_item->campaign_id = $campaign->id;
                                $element_item->user_id = $user_id;
                                $element_item->seat_id = 1;
                                $element_item->position_x = $value['position_x'];
                                $element_item->position_y = $value['position_y'];
                                $element_item->element_slug = $key;
                                $element_item->save();
                                $path_array[$key] = $element_item->id;
                                if (isset($final_data[$key])) {
                                    $property_item = $final_data[$key];
                                    foreach ($property_item as $key => $value) {
                                        $element_property = new UpdatedCampaignProperties();
                                        $property = ElementProperties::where('id', $key)->first();
                                        if ($property) {
                                            $element_property->element_id = $element_item->id;
                                            $element_property->property_id = $property->id;
                                            if ($value != null) {
                                                $element_property->value = $value;
                                            } else {
                                                $element_property->value = '';
                                            }
                                            $element_property->save();
                                        } else {
                                            return response()->json(['success' => false, 'properties' => 'Properties not found!']);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    foreach ($final_array as $key => $value) {
                        if (isset($path_array[$key])) {
                            $path = new CampaignPath();
                            $path->campaign_id = $campaign->id;
                            $path->current_element_id = $path_array[$key];
                            if ($final_array[$key]['0'] == '' && $final_array[$key]['1'] == '') {
                                continue;
                            } else if ($final_array[$key]['0'] == '') {
                                $path->next_true_element_id = $path_array[$value['1']];
                                $path->next_false_element_id = '';
                            } else if ($final_array[$key]['1'] == '') {
                                $path->next_true_element_id = '';
                                $path->next_false_element_id = $path_array[$value['0']];
                            } else {
                                $path->next_true_element_id = $path_array[$value['1']];
                                $path->next_false_element_id = $path_array[$value['0']];
                            }
                            $path->save();
                        }
                    }
                    if ($campaign->campaign_type == 'import') {
                        $imported_leads = ImportedLeads::where('user_id', $user_id)->first();
                        $fileHandle = fopen(storage_path('app/uploads/' . $imported_leads->file_path), 'r');
                        if ($fileHandle !== false) {
                            $csvData = [];
                            $delimiter = ',';
                            $enclosure = '"';
                            $escape = '\\';
                            $columnNames = fgetcsv($fileHandle, 0, $delimiter, $enclosure, $escape);
                            foreach ($columnNames as $colName) {
                                $csvData[$colName] = [];
                            }
                            while (($rowData = fgetcsv($fileHandle, 0, $delimiter, $enclosure, $escape)) !== false) {
                                foreach ($columnNames as $index => $colName) {
                                    $csvData[$colName][] = $rowData[$index] ?? null;
                                }
                            }
                            foreach ($csvData as $key => $value) {
                                foreach ($value as $url) {
                                    $lead = new Leads();
                                    $lead->is_active = 1;
                                    $lead->contact = '';
                                    $lead->title_company = '';
                                    $lead->send_connections = 'discovered';
                                    $lead->next_step = '';
                                    $lead->executed_time = date('H:i:s');
                                    $lead->campaign_id = $campaign->id;
                                    $lead->user_id = $user_id;
                                    $lead->created_at = now();
                                    $lead->updated_at = now();
                                    if (str_contains(strtolower($key), 'url')) {
                                        $lead->profileUrl = $url;
                                        $uc = new UnipileController();
                                        $profile = [
                                            'account_id' => $account_id,
                                            'profile_url' => $url
                                        ];
                                        $user_profile = $uc->view_profile(new \Illuminate\Http\Request($profile));
                                        if ($user_profile instanceof JsonResponse) {
                                            $user_profile = $user_profile->getData(true);
                                            if (!isset($user_profile['error'])) {
                                                $user_profile = $user_profile['user_profile'];
                                                if (isset($user_profile['first_name']) && isset($user_profile['last_name'])) {
                                                    $name = $user_profile['first_name'] . ' ' . $user_profile['last_name'];
                                                    $lead->title_company = $name;
                                                }
                                                if (isset($user_profile['name'])) {
                                                    $name = $user_profile['name'];
                                                    $lead->title_company = $name;
                                                }
                                                if (isset($user_profile['contact_info']['emails'])) {
                                                    $email = $user_profile['contact_info']['emails'][0];
                                                    $lead->email = $email;
                                                    $element = CampaignElement::where('element_slug', 'email_message')->first();
                                                    if (isset($element)) {
                                                        $campaign_element = UpdatedCampaignElements::where('campaign_id', $campaign->id)->where('element_id', $element->id)->first();
                                                        if (isset($campaign_element)) {
                                                            Mail::raw('', function ($message) use ($email) {
                                                                $message->to($email)
                                                                    ->subject('Your Lead is inserted Succesfully');
                                                            });
                                                        }
                                                    }
                                                }
                                                if (isset($user_profile['contact_info']['phones'])) {
                                                    $contact = $user_profile['contact_info']['phones'][0];
                                                    $lead->contact = $contact;
                                                }
                                                if (isset($user_profile['phone'])) {
                                                    $contact = $user_profile['phone'];
                                                    $lead->contact = $contact;
                                                }
                                                $lead->save();
                                                if (isset($user_profile['provider_id']) && $user_profile['is_relationship'] === true) {
                                                    $invite = [
                                                        'account_id' => $account_id,
                                                        'identifier' => $user_profile['provider_id'],
                                                    ];
                                                    $element = CampaignElement::where('element_slug', 'invite_to_connect')->first();
                                                    if (isset($element)) {
                                                        $campaign_element = UpdatedCampaignElements::where('campaign_id', $campaign->id)->where('element_id', $element->id)->first();
                                                        if (isset($campaign_element)) {
                                                            $campaign_property = UpdatedCampaignProperties::where('element_id', $campaign_element->id)->first();
                                                            if (isset($campaign_property)) {
                                                                $invite['message'] = $campaign_property->value;
                                                            }
                                                        }
                                                    }
                                                    // $invite_to_connect = $uc->invite_to_connect(new \Illuminate\Http\Request($invite));
                                                    // if ($invite_to_connect instanceof JsonResponse) {
                                                    //     $invite_to_connect = $invite_to_connect->getData(true);
                                                    //     $invite_to_connect = $invite_to_connect['invitaion'];
                                                    //     if (!isset($invite_to_connect['error'])) {
                                                    //         // $message = [
                                                    //         //     'account_id' => $account_id,
                                                    //         //     'identifier' => $user_profile['provider_id']
                                                    //         // ];
                                                    //         return response()->json(['success' => false, 'message' => $csvData['Info']]);
                                                    //     } else {
                                                    //         return response()->json(['success' => false, 'message' => $invite_to_connect['error'], 'user' => $user_profile]);
                                                    //     }
                                                    // } else {
                                                    //     return response()->json(['success' => false, 'message' => 'Invite to Connect not Json Response']);
                                                    // }
                                                }
                                            } else {
                                                return response()->json(['success' => false, 'message' => $user_profile['error']]);
                                            }
                                        } else {
                                            return response()->json(['success' => false, 'message' => 'User Profile not Json Response']);
                                        }
                                    } else if (str_contains(strtolower($key), 'email')) {
                                        $lead->email = $url;
                                        $lead->save();
                                    }
                                }
                            }
                            $request->session()->flash('success', 'Campaign succesfully saved!');
                            return response()->json(['success' => true]);
                        } else {
                            return response()->json(['success' => false, 'message' => 'File Not Found']);
                        }
                    } elseif ($campaign->campaign_type == 'sales_navigator') {
                        $uc = new UnipileController();
                        if (strpos($campaign->campaign_url, 'sales/search/people')) {
                            $request = [
                                'account_id' => $account_id
                            ];
                            $relations = $uc->get_relations(new \Illuminate\Http\Request($request));
                            if ($relations instanceof JsonResponse) {
                                $relations = $relations->getData(true);
                                if (!isset($relations['error'])) {
                                    $relations = $relations['relations'];
                                    $filters = $lead_details->query->filters;
                                    foreach ($filters as $key => $filter) {
                                        $type = $filter->type;
                                        $values = $filter->values;
                                        if ($type == "CURRENT_TITLE") {
                                            // $relations = $this->check_current_title($relations, $values);
                                            unset($filters[$key]);
                                        }
                                        if ($type == "PAST_TITLE") {
                                            // $relations = $this->check_past_title($relations, $values);
                                            unset($filters[$key]);
                                        }
                                        if ($type == "YEARS_AT_CURRENT_COMPANY") {
                                            $relations = $this->check_years_at_current_company($relations, $values);
                                            unset($filters[$key]);
                                        }
                                        if ($type == "YEARS_IN_CURRENT_POSITION") {
                                            // $relations = $this->check_years_in_current_position($relations, $values);
                                            unset($filters[$key]);
                                        }
                                    }
                                    return response()->json(['success' => false, 'relations' => $relations, 'filters' => $filters]);
                                } else {
                                    return response()->json(['success' => false, 'message' => $relations['error']]);
                                }
                            } else {
                                return response()->json(['success' => false, 'message' => 'Relations not Json Response']);
                            }
                        }
                    } else {
                        $request->session()->flash('success', 'Campaign succesfully saved!');
                        return response()->json(['success' => true]);
                    }
                } else {
                    Campaign::where('id', $campaign->id)->delete();
                    return response()->json(['success' => false, 'message' => 'Account Id Not Found']);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'No Campaign Inserted']);
            }
        } else {
            return redirect(url('/'));
        }
    }

    private function check_current_title($relations, $values)
    {
        $final_relations = array();
        foreach ($relations as $relation) {
            if (!empty($relation['work_experience'])) {
                foreach ($values as $value) {
                    if ($relation['work_experience'][0]['position'] == $value->text) {
                        $final_relations[] = $relation;
                    }
                }
            }
>>>>>>> seat_work
        }
        return $final_relations;
    }

<<<<<<< HEAD
    private function saveElementProperties($element_item_id, $property_item, $campaign_id, &$time)
    {
        foreach ($property_item as $property_id => $value) {
            $property = ElementProperties::find($property_id);

            if ($property) {
                $element_property = UpdatedCampaignProperties::create([
                    'element_id' => $element_item_id,
                    'property_id' => $property_id,
                    'campaign_id' => $campaign_id,
                    'value' => $value ?? '',
                ]);

                if ($element_property->value) {
                    $timeToAdd = intval($element_property->value);
                    if ($property->property_name == 'Hours') {
                        $time->addHours($timeToAdd);
                    } elseif ($property->property_name == 'Days') {
                        $time->addDays($timeToAdd);
=======
    private function check_past_title($relations, $values)
    {
        $final_relations = array();
        foreach ($relations as $relation) {
            if (!empty($relation['work_experience'])) {
                foreach ($values as $value) {
                    foreach ($relation['work_experience'] as $key => $experience) {
                        if ($key == 0) {
                            continue;
                        } else {
                            if ($experience['position'] == $value->text) {
                                $final_relations[] = $relation;
                            }
                        }
>>>>>>> seat_work
                    }
                }
            }
        }
<<<<<<< HEAD
    }

    private function createInitialCampaignAction($campaign_id)
    {
        $campaign_path = CampaignPath::where('campaign_id', $campaign_id)->first();
        CampaignActions::create([
            'current_element_id' => 'step_1',
            'next_true_element_id' => $campaign_path->current_element_id,
            'next_false_element_id' => '',
            'created_at' => now(),
            'updated_at' => now(),
            'campaign_id' => $campaign_id,
            'status' => 'inprogress',
            'ending_time' => now(),
        ]);
    }

    private function deleteCampaignData($campaign_id)
    {
        LinkedinSetting::where('campaign_id', $campaign_id)->delete();
        LeadActions::where('campaign_id', $campaign_id)->delete();
        Leads::where('campaign_id', $campaign_id)->delete();
        ImportedLeads::where('campaign_id', $campaign_id)->delete();
        GlobalSetting::where('campaign_id', $campaign_id)->delete();
        EmailSetting::where('campaign_id', $campaign_id)->delete();
        UpdatedCampaignProperties::where('campaign_id', $campaign_id)->delete();
        CampaignPath::where('campaign_id', $campaign_id)->delete();
        UpdatedCampaignElements::where('campaign_id', $campaign_id)->delete();
        CampaignActions::where('campaign_id', $campaign_id)->delete();
        Campaign::where('id', $campaign_id)->delete();
=======
        return $final_relations;
    }

    private function check_years_at_current_company($relations, $values)
    {
        $final_relations = array();
        foreach ($relations as $relation) {
            if (!empty($relation['work_experience'])) {
                foreach ($values as $value) {
                    $current_company = $relation['work_experience'][0]['company'];
                    $current_time = new DateTime();
                    $start_time = new DateTime($relation['work_experience'][0]['start']);
                    foreach ($relation['work_experience'] as $experience) {
                        if ($experience['company'] == $current_company) {
                            $start_time = new DateTime($experience['start']);
                        }
                    }
                    $tenure = date_diff($current_time, $start_time);
                    $final_relations[] = $tenure;
                }
            }
        }
        return $final_relations;
    }

    private function check_years_in_current_position($relations, $values)
    {
        $final_relations = array();
        foreach ($relations as $relation) {
            if (!empty($relation['work_experience'])) {
                foreach ($values as $value) {
                    $current = new DateTime();
                    $start = new DateTime($relation['work_experience'][0]['start']);
                    $tenure = date_diff($current, $start);
                    $final_relations[] = $tenure;
                }
            }
        }
        return $final_relations;
>>>>>>> seat_work
    }

    private function remove_prefix($value)
    {
        $reverse = strrev($value);
        $first_index = strpos($reverse, '_');
        $second_index = strlen($value) - $first_index - 1;
        $string = substr($value, 0, $second_index);
        return $string;
    }

    function getElements($campaign_id)
    {
<<<<<<< HEAD
        $elements = UpdatedCampaignElements::where('campaign_id', $campaign_id)->orderBy('id')->get();
        if (Auth::check()) {
=======
        if (Auth::check()) {
            $elements = UpdatedCampaignElements::where('campaign_id', $campaign_id)->orderBy('id')->get();
>>>>>>> seat_work
            foreach ($elements as $element) {
                $element['original_element'] = CampaignElement::where('id', $element->element_id)->first();
                $element['properties'] = UpdatedCampaignProperties::where('element_id', $element->id)->get();
                foreach ($element['properties'] as $property) {
                    $property['original_properties'] = ElementProperties::where('id', $property->property_id)->first();
                }
            }
            $path = CampaignPath::where('campaign_id', $campaign_id)->orderBy('id')->get();
            return response()->json(['success' => true, 'elements_array' => $elements, 'path' => $path]);
        } else {
            return redirect(url('/'));
        }
        $path = CampaignPath::where('campaign_id', $campaign_id)->orderBy('id')->get();
        return response()->json(['success' => true, 'elements_array' => $elements, 'path' => $path]);
    }

    function getcampaignelementbyid($element_id)
    {
        if (Auth::check()) {
            $properties = UpdatedCampaignProperties::where('element_id', $element_id)->get();
            if ($properties->isNotEmpty()) {
                foreach ($properties as $property) {
                    $property['original_properties'] = ElementProperties::where('id', $property->property_id)->first();
                }
                return response()->json(['success' => true, 'properties' => $properties]);
            } else {
                $element = CampaignElement::where('element_slug', $this->remove_prefix($element_id))->first();
                $properties = ElementProperties::where('element_id', $element->id)->get();
                return response()->json(['success' => false, 'properties' => $properties]);
            }
        } else {
            return redirect(url('/'));
        }
    }
}
