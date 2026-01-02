<ul class="time">
    <li>
        <p>App time : <?= AppTime::get_current_datetime() ?></p>
    </li>
    <li>
        <form method="post" action="time/advance">
            <input type="hidden" name="amount" value="1">
            <input type="hidden" name="unit" value="hour">
            <button type="submit">+ 1h</button>
        </form>
    </li>
    <li>
        <form method="post" action="time/advance">
            <input type="hidden" name="amount" value="1">
            <input type="hidden" name="unit" value="day">
            <button type="submit">+ 1d</button>
        </form>
    </li>
    <li>
        <form method="post" action="time/advance">
            <input type="hidden" name="amount" value="7">
            <input type="hidden" name="unit" value="day">
            <button type="submit">+ 1w</button>
        </form>
    </li>
    <li>
        <form method="post" action="time/reset">
            <button type="submit">Reset time offset</button>
        </form>
    </li>
</ul>