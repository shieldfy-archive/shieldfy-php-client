<?php
namespace Shieldfy\Jury;

class ScrubbingData
{
    private function filterData()
    {
        return [
            'address' => 'xxxxxxx',
            'password' => '*******',

            'credit-number' => '****-****-****-xxx',
            'credit-cvc' => '***'
        ];
    }

    private function filter($key, $value)
    {
        $data = $this->filterData();
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }
        return $value;
    }

    public function url($query)
    {
        $query = str_replace('/?', '', $query);
        parse_str($query, $get_array);
        $outData = '/?';
        foreach ($get_array as $key => $value) {
            $value = $this->filter($key, $value);
            $outData .= $key . '=' . $value . '&';
        }
        return substr($outData, 0, -1);
    }

    public function data($request_get = [])
    {
        $outData = [];
        foreach ($request_get as $key => $value) {
            $key_ = explode('.', $key)[1];
            $value = $this->filter($key_, $value);
            $outData [$key] = $value;
        }
        return $outData;
    }

    public function charge($charge = [])
    {
        $key_ = explode('.', $charge['key'])[1];
        $charge['value'] = $this->filter($key_, $charge['value']);
        return $charge;
    }
}
