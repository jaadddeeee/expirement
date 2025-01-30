<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvassearchOR" aria-labelledby="offcanvasBackdropLabel">
    <div class="offcanvas-header">
      <h5 id="offcanvasBackdropLabel" class="offcanvas-title"><i class = "fa fa-search"></i> Search OR</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <form id = "frmSearchOR">
      <div class="offcanvas-body my-auto mx-0 flex-grow-0">


        <label>Enter OR#</label>
        <input type = "number" id = "searchorno" name = "searchorno" class = "form-control mb-3" rows = "10" autofocus>
        <button type="button" id = "btnsearchor" class="btn btn-primary mb-2  w-100">Search</button>
        <button type="button" class="btn btn-outline-secondary  w-100" id = "btnORNOCancel" data-bs-dismiss="offcanvas">Cancel</button>

        <input type = "text" value = "" id = "hidORNo" hidden>
        <input type = "text" value = "" id = "hidDatePaid" hidden>
        <input type = "text" value = "" id = "hidButtonClick" hidden>
        <div class = "divider">
          <span class = "divider-text">Search result</span>
        </div>
        <div id = "searchorres"></div>
        <div class = "row">
          <div class="col-12">
            <div class="form-group">
              <label id="lblORNo">ORNo: </label>
            </div>
          </div>
        </div>

        <div class = "row">
          <div class="col-12">
            <div class="form-group">
              <label id="lblPayor">Payor: </label>
            </div>
          </div>
        </div>

        <div class = "row">
          <div class="col-12">
            <div class="form-group">
              <label id="lblDatePaid">Date Paid: </label>
            </div>
          </div>
        </div>

        <div class = "row">
          <div class="col-12">
            <div class="form-group">
              <label id="lblAmount">Amount: </label>
            </div>
          </div>
        </div>

        <div class = "row mt-2">
          <div class="col-12">
            <div class="form-group">
              <button id ="ORUseValue" class = "btn btn-primary btn-small">Use value</button>
            </div>
          </div>
        </div>

      </div>
    </form>
</div>
