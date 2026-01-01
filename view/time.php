<ul>
    <li>
        <form method="post" action="time/advance" style="display: inline;">
            <input type="hidden" name="amount" value="1">
            <input type="hidden" name="unit" value="hour">
            <button type="submit">Advance by 1 hour</button>
        </form>
    </li>
    <li>
        <form method="post" action="time/advance" style="display: inline;">
            <input type="hidden" name="amount" value="1">
            <input type="hidden" name="unit" value="day">
            <button type="submit">Advance by 1 day</button>
        </form>
    </li>
    <li>
        <form method="post" action="time/advance" style="display: inline;">
            <input type="hidden" name="amount" value="7">
            <input type="hidden" name="unit" value="day">
            <button type="submit">Advance by 1 week</button>
        </form>
    </li>
    <li>
        <form method="post" action="time/reset" style="display: inline;">
            <button type="submit">Reset time offset</button>
        </form>
    </li>
</ul>