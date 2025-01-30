<style>
  .option{
    display: none;
    margin-top: 5px;
  }
</style>

<div class="modal fade" id="modalSurveyQuestion" data-bs-backdrop="static" tabindex="-1" style="display: none;" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content"  id = "frmSurveyQuestion">
      <div class="modal-header">
        <h4 class="modal-title" id="backDropModalTitle">New Question</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          @csrf
          <input type = "text" hidden value = "{{Crypt::encryptstring($survey->id)}}" id = "hiddenSurveyID" name = "hiddenSurveyID">
          <input type = "text" hidden value = "" id = "hiddenQID" name = "hiddenQID">
          <div id="SurveyQuestionMsg"></div>
          <div class="row">
            <div class="col mb-2">
              <div class="form-floating form-floating-outline">
                <input type="text" id="question" name = "question" class="form-control" placeholder="Survey Title">
                <label for="question">Enter Question</label>
              </div>
            </div>
          </div>
          <div class="row g-2">
            <div class="col mb-2">
              <div class="form-floating form-floating-outline">
                <select class = "form-select" id = "type" name = "type">
                  <option></option>
                  <option value = "text">Short Text</option>
                  <option value = "number">Numeric</option>
                  <option value = "decimal">Amount  </option>
                  <option value = "checkbox">Checkbox</option>
                  <option value = "radio">Option Button</option>
                </select>
                <label for="date_start">Question Type</label>
              </div>
            </div>
          </div>
          @for($x=1;$x<=10;$x++)
          <div class="row option">
            <div class="col">
              <div class="form-floating form-floating-outline">
                <input  type = "text" id="option{{$x}}" name = "option{{$x}}" class="form-control" placeholder = "">
                <label for="option{{$x}}">Option {{$x}}</label>
              </div>
            </div>
          </div>
          @endfor
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary waves-effect" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary waves-effect waves-light">Save</button>
      </div>
    </form>
  </div>
</div>
