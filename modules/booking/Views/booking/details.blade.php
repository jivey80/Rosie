<form class="form-horizontal form-spacing">
    <div class="form-group label-floating">
        <label class="control-label" for="inputName">Full Name</label>
        <input class="form-control" id="inputName" type="text" tabindex="1" autofocus="autofocus" />
    </div>

    <div class="form-group label-floating">
        <label class="control-label" for="inputEmail">Email Address</label>
        <input class="form-control" id="inputEmail" type="email" tabindex="2" />
    </div>

    <!-- can use slider here -->
    <div class="form-group">
        <label for="inputHours" class="col-sm-6">How many hours needed?</label>
        <div class="col-sm-6" style="margin-top: -10px;">
            <select id="inputHours" class="form-control" tabindex="3" >
            @foreach ($hours as $hour)
                <option value="{{$hour}}">{{$hour}}</option>
            @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="inputSupplies" class="col-sm-6">Do you need the supplies?</label>
        <div class="col-sm-6">
            <div class="togglebutton">
                <label>
                    <input id="inputSupplies" type="checkbox" value="0" /> <span class="check-value">None</span>
                </label>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="inputInstruction" class="col-sm-6">Any special Instructions?</label>
        <div class="col-sm-6">
            <div class="togglebutton">
                <label>
                    <input id="inputInstruction" type="checkbox" value="0" /> <span class="check-value">None</span>
                </label>
            </div>
        </div>
    </div>

    <div class="form-group hidden" id="instruction_toggle">
        <textarea class="form-control" rows="6" id="txtInstruction" placeholder="Special Instructions"></textarea>
        <!-- <span class="help-block">A longer block of help text that breaks onto a new line and may extend beyond one line.</span> -->
    </div>
    
    <div class="form-footer">
        <div class="pull-right">
            <button type="button" class="btn btn-raised btn-info" id="details_next">Next</button>
        </div>

        <div class="pull-left">
            <!-- <div class="checkbox" style="margin-top: 5px;">
                <label>
                    <input type="checkbox"> &nbsp; Remember me
                </label>
            </div> -->
        </div>

        <div class="clearfix"></div>
    </div>
</form>