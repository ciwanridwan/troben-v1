<?php

namespace App\Jobs\Codes\Logs;

use App\Events\Codes\Logs\CodeLogged;
use App\Models\Code;
use App\Models\CodeLogable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateNewLog
{
    use Dispatchable;

    /**
     * @var Code
     */
    public Code $code;

    /**
     * @var Model
     */
    public Model $actedBy;

    /**
     * @var CodeLogable
     */
    public CodeLogable $codeLogable;

    protected array $attributes;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Code $code, Model $actedBy, array $inputs = [])
    {

        $this->attributes = Validator::validate($inputs, [
            'type' => ['required', Rule::in(CodeLogable::getAvailableTypes())],
            'status' => ['required', Rule::in(array_keys(CodeLogable::getAvailableStatusCode()))],
            'description' => ['nullable'],
            'showable.*' => ['required', Rule::in(CodeLogable::getAvailableShowable())],
        ]);
        $this->code = $code;
        $this->actedBy = $actedBy;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->attributes['code_id'] = $this->code->id;
        $this->attributes['showable'] = implode(',', $this->attributes['showable']);
        $this->codeLogable = $this->actedBy->code_logs()->create($this->attributes);
        event(new CodeLogged($this->codeLogable));
    }
}
