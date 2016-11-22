<p>
    A response has been submitted for the survey entitled <strong>{{survey.label}}</strong>. Please see below the
    questions and the submitted answer(s):
</p>
<hr>
<ul>
    {{#responses}}
        <li>
            <p><strong>{{{q}}}</strong></p>
            <p>{{{a}}}</p>
        </li>
    {{/responses}}
</ul>
