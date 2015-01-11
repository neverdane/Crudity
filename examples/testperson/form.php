<form id="testForm" class="cr-form" novalidate>
    <label>Name</label><input type="text" required="true" name="name"><br/>
    <label>Birthdate</label><input type="date" required="true" name="birthdate"><br/>
    <label>Biography</label><textarea name="biography"></textarea><br/>
    <label>Email</label><input type="email" required="true" name="email"><br/>
    <label>Phone</label><input type="tel" required="true" name="phone"><br/>
    <br/>
    <br/>
    <label>J'aime</label><input type="text" name="taste_name[]" cr-column="name" cr-entity="taste"><br/>
    <label>0 à 10 :</label><input type="number" name="taste_affection[]" cr-column="affection" cr-entity="taste"><br/>
    <br/>
    <br/>
    <label>J'aime</label><input type="text" name="taste_name[]" cr-column="name" cr-entity="taste"><br/>
    <label>0 à 10 :</label><input type="number" name="taste_affection[]" cr-column="affection" cr-entity="taste"><br/>
    <br/>
    <br/>
    <label>J'aime</label><input type="text" name="taste_name[]" cr-column="name" cr-entity="taste"><br/>
    <label>0 à 10 :</label><input type="number" name="taste_affection[]" cr-column="affection" cr-entity="taste"><br/>
    <br/>
    <br/>
    <label></label><input type="submit" value="Submit">
</form>